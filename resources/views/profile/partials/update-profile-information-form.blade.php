<div>
    <p class="text-muted mb-4">
        Update your account's profile information and email address.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label text-white">Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   style="background: rgba(255,255,255,0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #fff;">
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label text-white">Email</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="{{ old('email', $user->email) }}" required autocomplete="username"
                   style="background: rgba(255,255,255,0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #fff;">
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-warning small">
                        Your email address is unverified.
                        <button form="send-verification" class="btn btn-link btn-sm p-0 text-warning">
                            Click here to re-send the verification email.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success small">
                            A new verification link has been sent to your email address.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <i data-feather="save" class="me-1"></i>Save
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">
                    <i data-feather="check-circle" class="me-1"></i>Saved.
                </span>
            @endif
        </div>
    </form>
</div>
