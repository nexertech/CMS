@extends('layouts.sidebar')

@section('title', 'Change Password — CMS Admin')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card-glass" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header" style="padding: 20px; border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
                        <h4 class="text-white mb-0">
                            <i data-feather="lock" class="me-2" style="width: 20px; height: 20px;"></i>
                            Change Password
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 30px;">
                        @if ($errors->updatePassword->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error!</strong>
                                @foreach ($errors->updatePassword->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('status') === 'password-updated')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Success!</strong> Your password has been updated successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label text-white">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                    placeholder="Enter your current password"
                                    required
                                    style="background-color: rgba(255,255,255,0.08); border: 1px solid rgba(59, 130, 246, 0.4); color: #fff; height: 40px;"
                                >
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label text-white">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                    placeholder="Enter your new password"
                                    required
                                    style="background-color: rgba(255,255,255,0.08); border: 1px solid rgba(59, 130, 246, 0.4); color: #fff; height: 40px;"
                                >
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">
                                    Password must be at least 8 characters long and include uppercase, lowercase, numbers, and symbols.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label text-white">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                    placeholder="Confirm your new password"
                                    required
                                    style="background-color: rgba(255,255,255,0.08); border: 1px solid rgba(59, 130, 246, 0.4); color: #fff; height: 40px;"
                                >
                                @error('password_confirmation', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                                    <i data-feather="save" class="me-2" style="width: 16px; height: 16px;"></i>
                                    Update Password
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary" style="padding: 10px 24px; font-weight: 600;">
                                    <i data-feather="x" class="me-2" style="width: 16px; height: 16px;"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            background-color: rgba(255,255,255,0.12) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
            color: #fff !important;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
        }

        .text-danger {
            color: #ef4444;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 13px;
            margin-top: 4px;
        }

        .is-invalid {
            border-color: #ef4444 !important;
        }

        .is-invalid:focus {
            border-color: #ef4444 !important;
        }

        /* Light theme support */
        .theme-light .form-control {
            background-color: rgba(0, 0, 0, 0.05) !important;
            border: 1px solid rgba(0, 0, 0, 0.2) !important;
            color: #1e293b !important;
        }

        .theme-light .form-control::placeholder {
            color: #64748b;
        }

        .theme-light .form-control:focus {
            background-color: rgba(0, 0, 0, 0.08) !important;
            border-color: #3b82f6 !important;
            color: #1e293b !important;
        }

        .theme-light .form-label {
            color: #1e293b !important;
        }

        .theme-light .text-muted {
            color: #64748b !important;
        }
    </style>
@endsection
