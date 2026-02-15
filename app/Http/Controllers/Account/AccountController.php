<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function __construct(
        protected AuditService $audit
    ) {}

    /**
     * Show user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('employee.department', 'employee.position', 'employee.branch');

        return view('account.profile', compact('user'));
    }

    /**
     * Show change password form.
     */
    public function changePasswordForm()
    {
        return view('account.change-password');
    }

    /**
     * Handle password change.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password_hash' => Hash::make($validated['new_password']),
        ]);

        $this->audit->actionLog('account', 'change_password', 'success', [
            'user_id' => $user->id,
        ]);

        return redirect()->route('account.profile')
            ->with('success', 'Password changed successfully.');
    }
}
