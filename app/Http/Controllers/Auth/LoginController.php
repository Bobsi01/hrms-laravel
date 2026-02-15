<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(
        protected AuditService $audit
    ) {}

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'status' => 'active',
        ];

        // Auth::attempt uses getAuthPassword() which returns password_hash
        if (Auth::attempt(
            array_merge($credentials, ['password' => $request->input('password')]),
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();

            // Update last_login
            $user = Auth::user();
            $user->update(['last_login' => now()]);

            // Audit
            $this->audit->log('login', json_encode([
                'event' => 'login',
                'method' => 'password',
                'ip' => $request->ip(),
                'ua' => substr($request->userAgent() ?? '', 0, 300),
            ]));

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Invalid credentials or account is inactive.']);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        $this->audit->log('logout', 'User logged out');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
