@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="container-fluid">
        <h1 class="mt-4 mb-4">Notifications</h1>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('messages.notifications') }}</h5>
                
                @if($notifications->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-check-double me-1"></i> {{ __('messages.mark_all_as_read') }}
                    </button>
                </form>
                @endif
            </div>
            
            <div class="card-body">
                @if($notifications->count() > 0)
                    <ul class="list-group">
                        @foreach($notifications as $notification)
                            <li class="list-group-item {{ $notification->is_read ? '' : 'list-group-item-light' }} d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="d-flex align-items-center mb-1">
                                        @if($notification->icon)
                                            <i class="{{ $notification->icon }} me-2 text-{{ $notification->color ?? 'primary' }}"></i>
                                        @endif
                                        <div class="fw-bold">{{ __($notification->title) }}</div>
                                    </div>
                                    <p class="mb-1">{{ __($notification->message) }}</p>
                                    <small class="text-muted">
                                        {{ $notification->created_at->diffForHumans() }}
                                        @if($notification->is_read)
                                            &middot; Read {{ $notification->read_at->diffForHumans() }}
                                        @endif
                                    </small>
                                </div>
                                <div class="d-flex align-items-center">
                                    @if(!$notification->is_read)
                                    <form action="{{ route('notifications.mark-read', $notification) }}" method="POST" class="me-2">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if($notification->link)
                                    <a href="{{ $notification->link }}" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                    
                                    <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    
                    <div class="mt-4">
                        {{ $notifications->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <p class="lead">No notifications found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection 