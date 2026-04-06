@php($topbarNotifications = Auth::user()->notifications()->latest()->take(8)->get())
@php($topbarUnreadCount = Auth::user()->unreadNotifications()->count())

<div class="employee-content-topbar">
    <div class="employee-topbar-left">
        <span class="employee-topbar-context">{{ $context ?? 'Self-service workspace' }}</span>
    </div>
    <div class="employee-topbar-right">
        <div class="dropdown">
            <a href="#" class="employee-topbar-bell dropdown-toggle" data-toggle="dropdown" aria-label="Notifications">
                <i class="la la-bell"></i>
                @if($topbarUnreadCount > 0)
                    <span class="employee-topbar-bell-badge">{{ $topbarUnreadCount > 99 ? '99+' : $topbarUnreadCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu notifications dropdown-menu-right">
                <div class="topnav-dropdown-header">
                    <span class="notification-title">Notifications</span>
                    @if($topbarNotifications->isNotEmpty())
                        <a href="javascript:void(0)" class="clear-noti js-notification-clear">Clear All</a>
                    @endif
                </div>
                <div class="noti-content">
                    <ul class="notification-list">
                        @forelse($topbarNotifications as $item)
                            @php($itemData = (array) $item->data)
                            @php($itemUrl = trim((string) ($itemData['url'] ?? '')))
                            @php($itemTitle = trim((string) ($itemData['title'] ?? 'Notification')))
                            @php($itemMessage = trim((string) ($itemData['message'] ?? 'You have a new update.')))
                            @php($itemTone = in_array(($itemData['tone'] ?? 'info'), ['info', 'success', 'pending', 'negative'], true) ? (string) $itemData['tone'] : 'info')
                            <li class="notification-message {{ $item->read_at ? '' : 'bg-light' }}">
                                <a
                                    href="{{ $itemUrl !== '' ? $itemUrl : '#' }}"
                                    class="dropdown-item js-notification-item"
                                    data-read-url="{{ route('notifications/read', ['notificationId' => $item->id]) }}"
                                >
                                    <div class="media">
                                        <div class="media-body">
                                            <div class="notification-meta">
                                                <p class="noti-details mb-0"><span class="noti-title">{{ $itemTitle }}</span></p>
                                                <span class="notification-tone {{ $itemTone }}">{{ $itemTone }}</span>
                                            </div>
                                            <p class="noti-time mb-0">
                                                <span class="notification-time">{{ $itemMessage }}</span>
                                            </p>
                                            <small class="text-muted">{{ $item->created_at?->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="notification-message px-3 py-3 text-muted">No notifications yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="dropdown js-employee-profile-dropdown">
            <a href="#" class="employee-profile-pill dropdown-toggle js-employee-profile-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="employee-profile-avatar">
                <span>{{ Auth::user()->name }}</span>
                <i class="la la-angle-down employee-profile-caret" aria-hidden="true"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right employee-profile-menu js-employee-profile-menu">
                <a class="dropdown-item" href="{{ route('profile_user') }}">My Profile</a>
                <a class="dropdown-item" href="{{ route('change/password') }}">Change Password</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.__pcEmployeeProfileMenuBound) return;
        window.__pcEmployeeProfileMenuBound = true;

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('.js-employee-profile-trigger');
            var openMenu = document.querySelector('.js-employee-profile-menu.show');
            var openWrap = document.querySelector('.js-employee-profile-dropdown.show');

            if (trigger) {
                event.preventDefault();
                var wrap = trigger.closest('.js-employee-profile-dropdown');
                var menu = wrap ? wrap.querySelector('.js-employee-profile-menu') : null;
                if (!wrap || !menu) return;

                var shouldOpen = !menu.classList.contains('show');

                if (openMenu && openWrap && openMenu !== menu) {
                    openMenu.classList.remove('show');
                    openWrap.classList.remove('show');
                }

                menu.classList.toggle('show', shouldOpen);
                wrap.classList.toggle('show', shouldOpen);
                trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                return;
            }

            if (!event.target.closest('.js-employee-profile-dropdown')) {
                if (openMenu) openMenu.classList.remove('show');
                if (openWrap) openWrap.classList.remove('show');
                var activeTrigger = document.querySelector('.js-employee-profile-trigger[aria-expanded="true"]');
                if (activeTrigger) activeTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    })();
</script>
