<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="status-message success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username -->
        <div class="form-group">
            <label for="username" class="form-label">
                <i data-feather="user" class="inline w-4 h-4 mr-2"></i>Username
            </label>
            <input id="username" class="form-input" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Enter your username" />
            @error('username')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">
                <i data-feather="lock" class="inline w-4 h-4 mr-2"></i>Password
            </label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
            <input id="remember_me" type="checkbox" class="form-checkbox" name="remember">
            <label for="remember_me" style="font-size: 0.875rem; color: #6c757d; cursor: pointer; margin: 0;">Remember me</label>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-primary">
                <i data-feather="log-in" class="inline w-4 h-4"></i>
                Sign In
            </button>
        </div>

        <div style="text-align: center; margin-top: 1rem;">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-link">
                    Forgot your password?
                </a>
            @endif
        </div>

        <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e9ecef;">
            <p style="font-size: 0.875rem; color: #6c757d; margin-bottom: 0.5rem;">
                Don't have an account?
            </p>
            <a href="{{ route('register') }}" class="text-link" style="font-weight: 600;">
                Sign up here
            </a>
        </div>
    </form>
</x-guest-layout>
