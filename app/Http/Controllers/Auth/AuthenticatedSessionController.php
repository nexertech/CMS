<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View|RedirectResponse
    {
        // If user is already authenticated, redirect to admin dashboard
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Log the login
        \App\Models\LoginHistory::create([
            'user_id' => Auth::id(),
            'user_type' => get_class(Auth::user()),
            'username' => Auth::user()->username ?? Auth::user()->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'admin',
        ]);

        // Always direct backend (web guard) logins to the admin dashboard
        return redirect()->route('admin.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Only invalidate session if no other guard is logged in
        if (!Auth::guard('frontend')->check()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Redirect to admin login page instead of frontend
        return redirect()->route('login');
    }
}
