<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        // If user is already authenticated, redirect to admin dashboard
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 4, // Default to client role
                'status' => 'active',
            ]);

            // Log the user creation for debugging
            \Log::info('User created successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            ]);

            event(new Registered($user));

            Auth::login($user);

            // Redirect to admin dashboard if coming from admin register
            if (request()->is('admin/register') || request()->is('admin/*')) {
                return redirect()->route('admin.dashboard');
            }

            // Otherwise redirect to frontend dashboard
            return redirect()->route('frontend.dashboard');
        } catch (\Exception $e) {
            \Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }
}
