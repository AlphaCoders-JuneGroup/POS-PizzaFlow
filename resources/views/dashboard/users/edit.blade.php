@extends('layouts.dashboard')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('content')
<div class="pf-form-page">
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Edit User</h2>
        <p class="pf-dash-sub">Update profile details, role, password, and status.</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-pf-outline">
        <i class="bi bi-arrow-left me-1"></i> Back to users
    </a>
</div>

<div class="pf-dash-panel pf-user-form-panel">
    @if ($errors->any())
        <div class="alert alert-danger pf-alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 ps-3 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $editUser->_id) }}" id="editUserForm" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label pf-required" for="name">Full name</label>
                <div class="pf-input-icon">
                    <i class="bi bi-person"></i>
                    <input type="text" name="name" id="name"
                           class="form-control pf-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $editUser->name) }}" required minlength="2" maxlength="120">
                </div>
                <div class="invalid-feedback d-block" data-error-for="name">{{ $errors->first('name') }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label pf-required" for="phone">Phone</label>
                <div class="pf-input-icon">
                    <i class="bi bi-telephone"></i>
                    <input type="tel" name="phone" id="phone"
                           class="form-control pf-input @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $editUser->phone) }}" required
                           placeholder="0771234567">
                </div>
                <div class="form-text">Format: 0771234567 or +94771234567</div>
                <div class="invalid-feedback d-block" data-error-for="phone">{{ $errors->first('phone') }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label pf-required" for="email">Email</label>
                <div class="pf-input-icon">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" id="email"
                           class="form-control pf-input @error('email') is-invalid @enderror"
                           value="{{ old('email', $editUser->email) }}" required>
                </div>
                <div class="invalid-feedback d-block" data-error-for="email">{{ $errors->first('email') }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label pf-required" for="role">Role</label>
                <div class="pf-input-icon">
                    <i class="bi bi-shield-check"></i>
                    <select name="role" id="role" class="form-select pf-input @error('role') is-invalid @enderror" required>
                        @foreach ($roleOptions as $option)
                            <option value="{{ $option['value'] }}"
                                @selected(old('role', $editUser->role?->value ?? $editUser->role) === $option['value'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="invalid-feedback d-block" data-error-for="role">{{ $errors->first('role') }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password">New password</label>
                <div class="pf-input-icon pf-password-wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="password"
                           class="form-control pf-input @error('password') is-invalid @enderror"
                           minlength="8" placeholder="Leave blank to keep current"
                           autocomplete="new-password">
                    <button type="button" class="pf-password-toggle" data-toggle-password="password" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-text">Optional. Min 8 characters with letters and numbers.</div>
                <div class="invalid-feedback d-block" data-error-for="password">{{ $errors->first('password') }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirm password</label>
                <div class="pf-input-icon pf-password-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control pf-input" minlength="8"
                           placeholder="Re-enter new password" autocomplete="new-password">
                    <button type="button" class="pf-password-toggle" data-toggle-password="password_confirmation" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback d-block" data-error-for="password_confirmation"></div>
            </div>

            <div class="col-12">
                <div class="pf-active-box">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               @checked(old('is_active', $editUser->is_active))>
                        <label class="form-check-label" for="is_active">
                            <strong>Active account</strong>
                            <span class="d-block text-muted small">Inactive users cannot log in.</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-pf-primary">
                <i class="bi bi-check2-circle me-1"></i> Save changes
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-pf-outline">Cancel</a>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/user-form.js') }}"></script>
@endpush
