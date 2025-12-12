<div>
    <p class="text-muted mb-4">
        Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
    </p>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        <i data-feather="trash-2" class="me-1"></i>Delete Account
    </button>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.2);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(59, 130, 246, 0.2);">
                    <h5 class="modal-title text-white" id="deleteAccountModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <h6 class="text-white mb-3">Are you sure you want to delete your account?</h6>
                        <p class="text-muted mb-3">
                            Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                        </p>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label text-white">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required
                                   style="background: rgba(255,255,255,0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #fff;">
                            @error('password', 'userDeletion')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(59, 130, 246, 0.2);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i data-feather="trash-2" class="me-1"></i>Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
