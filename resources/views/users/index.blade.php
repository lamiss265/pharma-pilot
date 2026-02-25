@extends('layouts.app')

@section('title', __('messages.user_management'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.user_management') }}</h1>
        <a href="{{ route('users.create') }}" class="btn btn-success">
            <i class="fas fa-user-plus me-1"></i> {{ __('messages.add_user') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-users me-1"></i> {{ __('messages.user_list') }}
                </div>
                <div class="badge bg-primary">{{ count($users) }} {{ __('messages.users') }}</div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.id') }}</th>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.email') }}</th>
                            <th>{{ __('messages.role') }}</th>
                            <th>{{ __('messages.position') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.last_login') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 bg-{{ $user->role == 'admin' ? 'primary' : 'secondary' }} bg-opacity-10 rounded-circle">
                                        <span class="avatar-text text-{{ $user->role == 'admin' ? 'primary' : 'secondary' }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        @if($user->phone)
                                        <div class="small text-muted">{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role == 'admin' ? 'primary' : 'secondary' }}">
                                    {{ $user->role == 'admin' ? __('messages.admin') : __('messages.worker') }}
                                </span>
                            </td>
                            <td>{{ $user->position ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->status == 'active' ? 'success' : 'danger' }}">
                                    {{ $user->status == 'active' ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <span title="{{ $user->last_login_at }}">
                                        {{ $user->last_login_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">{{ __('messages.never_logged_in') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" title="{{ __('messages.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(Auth::id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('{{ __('messages.confirm_delete_user') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if(Auth::id() !== $user->id)
                                    <form action="{{ route('users.toggle-role', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $user->role == 'admin' ? 'secondary' : 'primary' }}" 
                                                title="{{ $user->role == 'admin' ? __('messages.demote_to_worker') : __('messages.promote_to_admin') }}"
                                                onclick="return confirm('{{ $user->role == 'admin' ? __('messages.confirm_demote') : __('messages.confirm_promote') }}')">
                                            <i class="fas fa-{{ $user->role == 'admin' ? 'arrow-down' : 'arrow-up' }}"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if(Auth::id() !== $user->id)
                                    <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $user->status == 'active' ? 'danger' : 'success' }}" 
                                                title="{{ $user->status == 'active' ? __('messages.deactivate') : __('messages.activate') }}"
                                                onclick="return confirm('{{ $user->status == 'active' ? __('messages.confirm_deactivate') : __('messages.confirm_activate') }}')">
                                            <i class="fas fa-{{ $user->status == 'active' ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-light">
            <i class="fas fa-info-circle me-1"></i> {{ __('messages.user_roles_info') }}
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">{{ __('messages.admin') }}</h5>
                    <p>{{ __('messages.admin_description') }}</p>
                    <ul>
                        <li>{{ __('messages.admin_access1') }}</li>
                        <li>{{ __('messages.admin_access2') }}</li>
                        <li>{{ __('messages.admin_access3') }}</li>
                        <li>{{ __('messages.admin_access4') }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="card-title">{{ __('messages.worker') }}</h5>
                    <p>{{ __('messages.worker_description') }}</p>
                    <ul>
                        <li>{{ __('messages.worker_access1') }}</li>
                        <li>{{ __('messages.worker_access2') }}</li>
                        <li>{{ __('messages.worker_access3') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable with custom options
        if ($.fn.dataTable.isDataTable('#usersTable')) {
            $('#usersTable').DataTable().destroy();
        }
        
        $('#usersTable').DataTable({
            "order": [[0, "asc"]],
            "pageLength": 25,
            "autoWidth": false
        });
    });
</script>
@endsection