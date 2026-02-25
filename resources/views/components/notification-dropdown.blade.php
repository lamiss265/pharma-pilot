@php
    $notificationService = app('notification');
    $unreadCount = $notificationService->getUnreadCount();
    $recentNotifications = $notificationService->getRecentNotifications(5);
@endphp

<li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>
    
    <div class="dropdown-menu dropdown-menu-end shadow p-0" aria-labelledby="notificationsDropdown" style="width: 320px; max-height: 400px; overflow-y: auto; border-radius: 0.5rem;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
            <h6 class="mb-0 fw-bold">{{ __('messages.notifications') }}</h6>
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none text-primary">
                        {{ __('messages.mark_all_as_read') }}
                    </button>
                </form>
            @endif
        </div>
        
        @if($recentNotifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($recentNotifications as $notification)
                    <div class="list-group-item list-group-item-action p-3 {{ $notification->is_read ? '' : 'bg-light' }} border-0">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center">
                                @if($notification->icon)
                                    <div class="rounded-circle bg-{{ $notification->color ?? 'primary' }}-subtle p-2 me-2">
                                        <i class="{{ $notification->icon }} text-{{ $notification->color ?? 'primary' }}"></i>
                                    </div>
                                @endif
                                <h6 class="mb-0 fw-semibold">{{ __($notification->title) }}</h6>
                            </div>
                            <small class="text-muted ms-2">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1 text-truncate small">{{ __($notification->message) }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            @if($notification->link)
                                <a href="{{ $notification->link }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-eye me-1"></i> {{ __('messages.view_details') }}
                                </a>
                            @else
                                <div></div>
                            @endif
                            
                            @if(!$notification->is_read)
                                <form action="{{ route('notifications.mark-read', $notification) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
                                        <i class="fas fa-check me-1"></i> {{ __('messages.mark_as_read') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="p-2 text-center border-top">
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-link text-decoration-none">
                    {{ __('messages.view_all_notifications') }} <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        @else
            <div class="p-4 text-center">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-bell-slash fa-2x text-muted"></i>
                </div>
                <p class="text-muted">{{ __('messages.no_notifications') }}</p>
            </div>
        @endif
    </div>
</li> 