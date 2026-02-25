@extends('layouts.app')

@section('title', __('messages.change_password'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.change_password') }}</h1>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back') }}
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <i class="fas fa-key me-1"></i> {{ __('messages.change_password_for', ['name' => $user->name]) }}
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update-password', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @if(Auth::id() === $user->id)
                        <div class="mb-3">
                            <label for="current_password" class="form-label">{{ __('messages.current_password') }} <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('messages.new_password') }} <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('messages.password_requirements') }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">{{ __('messages.confirm_new_password') }} <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> {{ __('messages.update_password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 