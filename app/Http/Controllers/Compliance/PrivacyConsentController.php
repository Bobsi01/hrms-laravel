<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\PrivacyConsent;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivacyConsentController extends Controller
{
    public function __construct(
        protected AuditService $audit
    ) {}

    /**
     * Show the privacy consent / data privacy notice page.
     */
    public function show()
    {
        $user = Auth::user();
        $consentTypes = PrivacyConsent::consentTypes();

        // Get existing consents for this user
        $existingConsents = PrivacyConsent::where('user_id', $user->id)
            ->pluck('consented', 'consent_type')
            ->toArray();

        return view('compliance.privacy.consent', [
            'pageTitle'        => 'Data Privacy Notice',
            'consentTypes'     => $consentTypes,
            'existingConsents' => $existingConsents,
            'allRequired'      => $this->hasAllRequiredConsents($user->id),
        ]);
    }

    /**
     * Store or update consent responses.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $consentTypes = PrivacyConsent::consentTypes();
        $consents = $request->input('consents', []);

        foreach ($consentTypes as $type => $config) {
            $consented = in_array($type, $consents);

            PrivacyConsent::updateOrCreate(
                ['user_id' => $user->id, 'consent_type' => $type],
                [
                    'consented'    => $consented,
                    'consented_at' => $consented ? now() : null,
                    'withdrawn_at' => !$consented ? now() : null,
                    'ip_address'   => $request->ip(),
                    'user_agent'   => substr($request->userAgent() ?? '', 0, 500),
                    'version'      => 1,
                ]
            );
        }

        $this->audit->actionLog('privacy_consent', 'update', 'success', [
            'consents' => $consents,
            'ip'       => $request->ip(),
        ]);

        return redirect()->route('privacy.consent')
            ->with('success', 'Your privacy preferences have been saved.');
    }

    /**
     * Withdraw a specific consent.
     */
    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'consent_type' => 'required|string|max:50',
        ]);

        $consent = PrivacyConsent::where('user_id', $user->id)
            ->where('consent_type', $validated['consent_type'])
            ->first();

        if ($consent) {
            $consent->update([
                'consented'    => false,
                'withdrawn_at' => now(),
                'ip_address'   => $request->ip(),
                'user_agent'   => substr($request->userAgent() ?? '', 0, 500),
            ]);

            $this->audit->actionLog('privacy_consent', 'withdraw', 'success', [
                'consent_type' => $validated['consent_type'],
                'ip'           => $request->ip(),
            ]);
        }

        return redirect()->route('privacy.consent')
            ->with('success', 'Consent withdrawn successfully.');
    }

    /**
     * Admin: view consent summary across all users.
     */
    public function admin()
    {
        $consentTypes = PrivacyConsent::consentTypes();
        $stats = [];

        foreach ($consentTypes as $type => $config) {
            $stats[$type] = [
                'label'     => $config['label'],
                'required'  => $config['required'],
                'consented' => PrivacyConsent::where('consent_type', $type)->where('consented', true)->count(),
                'declined'  => PrivacyConsent::where('consent_type', $type)->where('consented', false)->count(),
                'pending'   => 0, // computed below
            ];
        }

        $totalUsers = \App\Models\User::count();
        foreach ($stats as $type => &$data) {
            $data['pending'] = $totalUsers - $data['consented'] - $data['declined'];
        }

        $recentActivity = PrivacyConsent::with('user')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        return view('compliance.privacy.admin', [
            'pageTitle'      => 'Privacy Consent Dashboard',
            'consentTypes'   => $consentTypes,
            'stats'          => $stats,
            'recentActivity' => $recentActivity,
            'totalUsers'     => $totalUsers,
        ]);
    }

    /**
     * Check if user has given all required consents.
     */
    private function hasAllRequiredConsents(int $userId): bool
    {
        $required = collect(PrivacyConsent::consentTypes())
            ->filter(fn($c) => $c['required'])
            ->keys();

        $given = PrivacyConsent::where('user_id', $userId)
            ->where('consented', true)
            ->pluck('consent_type');

        return $required->diff($given)->isEmpty();
    }
}
