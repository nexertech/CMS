<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
        ]);

        // Explicitly extract as string to prevent array injection
        $email = (string) $request->input('email');

        // Apply rate limiting (e.g., 3 attempts per minute per IP) to prevent brute-force attacks
        $executed = \Illuminate\Support\Facades\RateLimiter::attempt(
            'send-password-reset:'.$request->ip(),
            $perMinute = 3,
            function() use ($email) {
                Password::sendResetLink(['email' => $email]);
            }
        );

        if (! $executed) {
            return back()->withErrors(['email' => __('Too many requests. Please try again later.')]);
        }

        // Return a generic response regardless of whether the email exists to prevent enumeration
        return back()->with('status', __('If the email exists, a reset link has been sent.'));
    }
}
