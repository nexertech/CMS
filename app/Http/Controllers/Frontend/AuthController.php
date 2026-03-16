<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    public function showRegister()
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required','string','max:100','unique:users,username'],
            'email' => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:6','confirmed'],
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 2, // default to Employee/standard role if exists
            'status' => 1,
            'theme' => 'light',
        ]);

        Auth::guard('frontend')->login($user);

        return redirect()->route('frontend.dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::guard('frontend')->attempt($credentials, false)) {
            // Check if user is active
            $user = Auth::guard('frontend')->user();
            
            if ($user->status !== 1) {
                Auth::guard('frontend')->logout();
                
                return back()->withErrors([
                    'username' => 'Your account has been deactivated. Please contact the administrator.',
                ])->onlyInput('username');
            }
            
            $request->session()->regenerate();

            // Log the login
            \App\Models\LoginHistory::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'username' => $user->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'source' => 'web',
            ]);

            return redirect()->route('frontend.dashboard');
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::guard('frontend')->logout();

        // Only invalidate session if no other guard is logged in
        if (!Auth::guard('web')->check()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('frontend.home');
    }

    public function showForgotPassword()
    {
        return view('frontend.auth.forgot-password');
    }
}


