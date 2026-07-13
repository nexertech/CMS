<div>
    <p class="text-muted mb-4">
        Ensure your account is using a long, random password to stay secure.
    </p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label text-white fw-semibold">Current Password</label>
            <input type="password" class="form-control profile-form-control" id="update_password_current_password" 
                   name="current_password" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label text-white fw-semibold">New Password</label>
            <input type="password" class="form-control profile-form-control" id="update_password_password" 
                   name="password" autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label text-white fw-semibold">Confirm Password</label>
            <input type="password" class="form-control profile-form-control" id="update_password_password_confirmation" 
                   name="password_confirmation" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <i data-feather="save" class="me-1"></i>Save
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success small">
                    <i data-feather="check-circle" class="me-1"></i>Saved.
                </span>
            @endif
        </div>
    </form>
</div>
