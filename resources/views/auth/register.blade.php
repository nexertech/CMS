<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Username -->
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="username" class="form-label" style="margin-bottom: 0.375rem; font-size: 0.8125rem;">
                <i data-feather="user" class="inline w-3.5 h-3.5 mr-1.5"></i>Username
            </label>
            <input id="username" class="form-input" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Choose a username" style="padding: 0.625rem 0.875rem; font-size: 0.875rem;" />
            @error('username')
                <div class="error-message" style="margin-top: 0.375rem; font-size: 0.75rem;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="email" class="form-label" style="margin-bottom: 0.375rem; font-size: 0.8125rem;">
                <i data-feather="mail" class="inline w-3.5 h-3.5 mr-1.5"></i>Email Address
            </label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Enter your email" style="padding: 0.625rem 0.875rem; font-size: 0.875rem;" />
            @error('email')
                <div class="error-message" style="margin-top: 0.375rem; font-size: 0.75rem;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="password" class="form-label" style="margin-bottom: 0.375rem; font-size: 0.8125rem;">
                <i data-feather="lock" class="inline w-3.5 h-3.5 mr-1.5"></i>Password
            </label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password" placeholder="Create a password" style="padding: 0.625rem 0.875rem; font-size: 0.875rem;" />
            @error('password')
                <div class="error-message" style="margin-top: 0.375rem; font-size: 0.75rem;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="password_confirmation" class="form-label" style="margin-bottom: 0.375rem; font-size: 0.8125rem;">
                <i data-feather="lock" class="inline w-3.5 h-3.5 mr-1.5"></i>Confirm Password
            </label>
            <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password" style="padding: 0.625rem 0.875rem; font-size: 0.875rem;" />
            @error('password_confirmation')
                <div class="error-message" style="margin-top: 0.375rem; font-size: 0.75rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <button type="submit" class="btn-primary" style="padding: 0.625rem 1.25rem; font-size: 0.875rem;">
                <i data-feather="user-plus" class="inline w-3.5 h-3.5"></i>
                Create Account
            </button>
        </div>

        <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e9ecef;">
            <p style="font-size: 0.8125rem; color: #6c757d; margin-bottom: 0.375rem;">
                Already have an account?
            </p>
            <a href="{{ route('login') }}" class="text-link" style="font-weight: 600; font-size: 0.8125rem;">
                Sign in here
            </a>
        </div>
    </form>
</x-guest-layout>
