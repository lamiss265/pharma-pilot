@extends('layouts.app')

@section('title', __('messages.edit_user'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.edit_user') }}</h1>
        <div>
            <a href="{{ route('users.change-password', $user) }}" class="btn btn-info me-2">
                <i class="fas fa-key me-1"></i> {{ __('messages.change_password') }}
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <i class="fas fa-user-edit me-1"></i> {{ __('messages.user_details') }}
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="role" class="form-label">{{ __('messages.role') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="worker" {{ old('role', $user->role) == 'worker' ? 'selected' : '' }}>{{ __('messages.worker') }}</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="language" class="form-label">{{ __('messages.language') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                            <option value="en" {{ old('language', $user->language) == 'en' ? 'selected' : '' }}>{{ __('messages.english') }}</option>
                            <option value="fr" {{ old('language', $user->language) == 'fr' ? 'selected' : '' }}>{{ __('messages.french') }}</option>
                            <option value="ar" {{ old('language', $user->language) == 'ar' ? 'selected' : '' }}>{{ __('messages.arabic') }}</option>
                        </select>
                        @error('language')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="status" class="form-label">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="position" class="form-label">{{ __('messages.position') }}</label>
                        <input type="text" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', $user->position) }}">
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">{{ __('messages.address') }}</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.permissions') }}</label>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_inventory" name="permissions[]" value="inventory" 
                                    {{ in_array('inventory', old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_inventory">{{ __('messages.manage_inventory') }}</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_sales" name="permissions[]" value="sales" 
                                    {{ in_array('sales', old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_sales">{{ __('messages.manage_sales') }}</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_clients" name="permissions[]" value="clients" 
                                    {{ in_array('clients', old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_clients">{{ __('messages.manage_clients') }}</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="perm_reports" name="permissions[]" value="reports" 
                                    {{ in_array('reports', old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_reports">{{ __('messages.view_reports') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ __('messages.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    @if($user->last_login_at)
    <div class="card mt-4">
        <div class="card-header bg-light">
            <i class="fas fa-history me-1"></i> {{ __('messages.login_history') }}
        </div>
        <div class="card-body">
            <p>{{ __('messages.last_login_at') }}: <strong>{{ $user->last_login_at->format('Y-m-d H:i:s') }}</strong></p>
            <p>{{ __('messages.last_login_ip') }}: <strong>{{ $user->last_login_ip ?? __('messages.not_available') }}</strong></p>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle permissions based on role
        $('#role').change(function() {
            if ($(this).val() === 'admin') {
                $('input[name="permissions[]"]').prop('checked', true);
            }
        });
    });
</script>
@endsection 