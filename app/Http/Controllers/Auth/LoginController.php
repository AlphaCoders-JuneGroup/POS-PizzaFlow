<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user && $user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account has been deactivated. Contact support.',
            ]);
        }

        // Clear guest checkout session after a real login
        $request->session()->forget(['guest_checkout', 'guest']);

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'login_count' => (int) ($user->login_count ?? 0) + 1,
        ])->save();

        return redirect()->intended(route($user->dashboardRoute()))
            ->with('success', 'Welcome back, '.$user->name.'!');
    }
}
