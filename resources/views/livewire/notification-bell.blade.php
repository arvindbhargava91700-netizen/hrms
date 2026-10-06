<div class="nav-item dropdown">
    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding-top: 10px; padding-bottom: 10px;">
        <i class="bi bi-bell fs-5"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>
    
    <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width: 350px; max-height: 500px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
            <h6 class="mb-0 fw-bold">Notifications</h6>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="btn btn-sm btn-link text-decoration-none p-0 text-primary">Mark all as read</button>
            @endif
        </div>
        
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <div class="list-group-item list-group-item-action p-3 {{ $notification->read_at ? 'bg-white text-muted' : 'bg-light' }}">
                    <div class="d-flex gap-3">
                        @if($notification->image)
                            <div class="flex-shrink-0">
                                <img src="{{ $notification->image }}" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                            </div>
                        @else
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width: 40px; height: 40px;">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                        @endif
                        
                        <div class="flex-grow-1 min-w-0" style="cursor: pointer;" wire:click="markAsRead('{{ $notification->id }}')">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-truncate {{ $notification->read_at ? 'fw-normal' : 'fw-bold' }}" style="font-size: 0.9rem;">
                                    {{ $notification->title }}
                                </h6>
                                <small class="text-nowrap ms-2" style="font-size: 0.75rem;">
                                    {{ $notification->created_at->diffForHumans(null, true, true) }}
                                </small>
                            </div>
                            <p class="mb-0 text-break" style="font-size: 0.85rem; line-height: 1.4;">
                                {{ \Illuminate\Support\Str::limit($notification->message, 80) }}
                            </p>
                        </div>
                        
                        <div class="ms-2 d-flex flex-column justify-content-center align-items-center">
                            @if(!$notification->read_at)
                                <div class="bg-primary rounded-circle mb-2" style="width: 8px; height: 8px;"></div>
                            @endif
                            <button wire:click.stop="deleteNotification('{{ $notification->id }}')" class="btn btn-sm btn-link text-muted p-0 border-0" title="Delete">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-bell-slash fs-3 mb-2 d-block text-black-50"></i>
                    <p class="mb-0 small">No notifications yet.</p>
                </div>
            @endforelse
        </div>
        
        @if(count($notifications) >= 10)
            <div class="p-2 text-center border-top bg-light">
                <span class="small text-muted">Showing latest 10</span>
            </div>
        @endif
    </div>
</div>
