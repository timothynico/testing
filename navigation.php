{{-- ============================================================================
NAVIGATION COMPONENT
- Mobile Navigation: < 992px (Bootstrap lg breakpoint)
- Desktop Navigation: ≥ 992px
- Shared menu items defined once, reused by both
- Role-based menu visibility
- Notifications feature
============================================================================ --}}

@php
    $chatRoomIds = \App\Models\ChatRoomDetail::query()
        ->where('niduser', Auth::id())
        ->pluck('nidchatroom')
        ->map(fn($id) => (int) $id)
        ->values()
        ->all();

    $isNetwork = request()->routeIs('agreement.*');
    $isTransaction = request()->routeIs('delivery.*');
    $isFinance = request()->routeIs('finance.*', 'invoice.*');
    $isManagement = request()->routeIs('logisticcompany.*', 'customers.*', 'warehouse.*', 'marketing.*', 'pallet.*');
    $isReports = request()->routeIs('report.*');

    // Get user role
    $userRole = Auth::user()->role ?? 'user';
    $isAdmin = $userRole === 'admin';
    $isWarehouseYs = $userRole === 'warehouseys';
    $isWarehousePic = Auth::user()->customer_role === 'warehouse_pic';
@endphp

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid px-3 px-lg-4 position-relative">

        {{-- ============================================================================
        MOBILE NAVIGATION (< 992px)
        ============================================================================ --}}
        <div class="d-lg-none w-100">
            {{-- Mobile Header Row --}}
            <div class="d-flex align-items-center justify-content-between">
                {{-- Hamburger Menu (Left) --}}
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                {{-- Logo + Title (Center) --}}
                <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('dashboard.customer') }}"
                    class="navbar-brand position-absolute top-50 start-50 translate-middle text-center">
                    @if (file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
                    @endif
                    <span>{{ config('app.name', 'Yanarental') }}</span>
                </a>

                <div class="d-flex align-items-center gap-2">
                    {{-- Notifications (Mobile) --}}
                    <div class="dropdown">
                        <a class="nav-link position-relative p-0" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-bell fs-5"></i>
                            @if ($notificationsUnreadCount > 0)
                                <span class="position-absolute badge rounded-pill bg-danger notification-badge"
                                    style="top: 4px; right: 4px;">
                                    {{ $notificationsUnreadCount > 99 ? '99+' : $notificationsUnreadCount }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <li>
                                <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                    {{ __('Notifications') }}
                                    @if (count($notifications) > 0)
                                        <a href="#"
                                            class="notification-action-link">{{ __('Mark all as read') }}</a>
                                    @endif
                                </h6>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-0">
                            </li>
                            @forelse ($notifications as $notification)
                                <li>
                                    <a class="dropdown-item notification-item {{ $notification->lisread ? 'read' : 'unread' }}"
                                        href="{{ url($notification->curl) }}">
                                        <div class="d-flex">
                                            <div class="notification-icon">
                                                @if ($notification['type'] == 'agreement')
                                                    <i class="bi bi-file-text-fill"></i>
                                                @elseif ($notification['type'] == 'incoming')
                                                    <i class="bi bi-truck-front-fill"></i>
                                                @elseif ($notification['type'] == 'arrival')
                                                    <i class="bi bi-check-circle-fill"></i>
                                                @endif
                                            </div>
                                            <div class="notification-content">
                                                <div class="notification-title-row">
                                                    <div class="notification-title">{{ $notification['title'] }}</div>
                                                    <div class="notification-time">5 minutes ago</div>
                                                </div>
                                                <div class="notification-text">{{ $notification['text'] }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li>
                                    <div class="dropdown-item text-center py-4 text-muted">
                                        <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                        No notifications available
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Language Switcher (Mobile) --}}
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center p-0" href="#"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-globe2"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                    href="{{ route('lang.switch', 'en') }}">
                                    <span class="me-2">🇬🇧</span> English
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}"
                                    href="{{ route('lang.switch', 'id') }}">
                                    <span class="me-2">🇮🇩</span> Indonesia
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'cn' ? 'active' : '' }}"
                                    href="{{ route('lang.switch', 'cn') }}">
                                    <span class="me-2">cn</span> Chinese
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- User Dropdown (Right) --}}
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center p-0" href="#"
                            data-bs-toggle="dropdown">
                            <span class="fw-semibold small mobile-username">
                                {{ Auth::user()->name }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person"></i>{{ __('Profile') }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i>{{ __('Log Out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Mobile Collapsible Menu --}}
            <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav">
                {{-- Mobile Search Box --}}
                <div class="search-box-mobile">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="mobileSearch"
                        placeholder="{{ __('Search menus...') }}" autocomplete="off" autocorrect="off"
                        autocapitalize="off" spellcheck="false" />
                </div>

                {{-- Mobile Menu Items (Reuses shared menu structure) --}}
                <ul class="navbar-nav" id="mobileMenuList" data-menu-source="shared"></ul>
            </div>
        </div>

        {{-- ============================================================================
        DESKTOP NAVIGATION (≥ 992px)
        ============================================================================ --}}
        <div class="d-none d-lg-flex w-100 align-items-center">
            {{-- Logo + Title (Left) --}}
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                @if (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
                @endif
                <span>{{ config('app.name', 'Yanarental') }}</span>
            </a>

            {{-- Desktop Menu Items (Reuses shared menu structure) --}}
            <ul class="navbar-nav me-auto" id="desktopMenuList" data-menu-source="shared"></ul>

            {{-- Desktop Right Menu --}}
            <ul class="navbar-nav align-items-center">
                {{-- Desktop Search Box --}}
                <li class="nav-item me-3">
                    <div class="search-box position-relative">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="desktopSearch"
                            placeholder="{{ __('Search menus...') }}" autocomplete="off" autocorrect="off"
                            autocapitalize="off" spellcheck="false" />

                        {{-- Desktop Search Results Dropdown --}}
                        <div id="desktopSearchResults" class="search-suggestions d-none"></div>
                    </div>
                </li>

                {{-- Notifications (Desktop) --}}
                <li class="nav-item dropdown me-2">
                    <a class="nav-link position-relative d-flex align-items-center" href="#"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        @if ($notificationsUnreadCount > 0)
                            <span class="position-absolute badge rounded-pill bg-danger"
                                style="top: 4px; right: 4px;">
                                {{ $notificationsUnreadCount > 99 ? '99+' : $notificationsUnreadCount }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <li>
                            <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                {{ __('Notifications') }}
                                @if (count($notifications) > 0)
                                    <form action="{{ route('notifications.readAll') }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-link p-0 text-success small">
                                            {{ __('Mark all as read') }}
                                        </button>
                                    </form>
                                @endif
                            </h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider my-0">
                        </li>
                        @forelse ($notifications as $notification)
                            <li>
                                <a class="dropdown-item notification-item {{ $notification->lisread ? 'read' : 'unread' }}"
                                    href="{{ route('notifications.read', $notification->nidnotif) }}">
                                    <div class="d-flex">
                                        <div class="notification-icon">
                                            @if ($notification->ctype == 'agreement')
                                                <i class="bi bi-file-text-fill"></i>
                                            @elseif ($notification->ctype == 'incoming')
                                                <i class="bi bi-truck-front-fill"></i>
                                            @elseif ($notification->ctype == 'arrival')
                                                <i class="bi bi-check-circle-fill"></i>
                                            @endif
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-title-row">
                                                <div class="notification-title">{{ $notification->ctitle }}</div>
                                                <div class="notification-time">
                                                    {{ $notification->created_at->diffForHumans() }}</div>
                                            </div>
                                            <div class="notification-text">{{ $notification->ctext }}</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li>
                                <div class="dropdown-item text-center py-4 text-muted">
                                    <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                    No notifications available
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </li>

                {{-- Language Switcher (Desktop) --}}
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-globe2 me-1"></i>
                        @switch(app()->getLocale())
                            @case('id')
                                <span>ID</span>
                            @break

                            @case('en')
                                <span>EN</span>
                            @break

                            @case('cn')
                                <span>CN</span>
                            @break

                            @default
                                <span>{{ app()->getLocale() }}</span>
                        @endswitch
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                href="{{ route('lang.switch', 'en') }}">
                                <span class="me-2">🇬🇧</span> English
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}"
                                href="{{ route('lang.switch', 'id') }}">
                                <span class="me-2">🇮🇩</span> Indonesia
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ app()->getLocale() === 'cn' ? 'active' : '' }}"
                                href="{{ route('lang.switch', 'cn') }}">
                                <span class="me-2">cn</span> Chinese
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Desktop User Dropdown --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                        data-bs-toggle="dropdown">
                        <div
                            class="rounded-circle text-white d-flex align-items-center justify-content-center me-2 user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person"></i>{{ __('Profile') }}
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i>{{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</nav>

{{-- ============================================================================
    SHARED MENU STRUCTURE
    - Defined once, cloned into both mobile and desktop navigation
    - Hidden template that gets duplicated by JavaScript
    - Role-based visibility controlled via data-roles attribute
    ============================================================================ --}}
<template id="sharedMenuTemplate">
    @if (!$isWarehouseYs && !$isWarehousePic)
        {{-- Network --}}
        <li class="nav-item dropdown" data-roles="admin" data-tour="menu-network">
            <a class="nav-link dropdown-toggle {{ $isNetwork ? 'active' : '' }}" href="#"
                data-bs-toggle="dropdown">
                <i class="bi bi-diagram-3"></i>{{ __('Network') }}
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="{{ route('agreement.index') }}" data-keywords="agreements"
                        data-tour="agreement-link">
                        <i class="bi bi-file-earmark-text"></i>{{ __('Agreements') }}
                    </a>
                </li>
            </ul>
        </li>
    @endif


    {{-- Transaction --}}
    <li class="nav-item dropdown" data-roles="admin" data-tour="menu-transaction">
        <a class="nav-link dropdown-toggle {{ $isTransaction ? 'active' : '' }}" href="#"
            data-bs-toggle="dropdown">
            <i class="bi bi-repeat"></i>{{ __('Transaction') }}
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('delivery.index') }}" data-keywords="delivery monitoring">
                    <i class="bi bi-geo-alt"></i>{{ __('Delivery Monitoring') }}</a></li>
            <li><a class="dropdown-item" href="{{ route('delivery.create') }}"
                    data-keywords="add delivery note new delivery note">
                    <i class="bi bi-file-text"></i>{{ __('New Delivery Note') }}
                </a></li>
            <li><a class="dropdown-item" href="{{ route('delivery.goods_receipt_create') }}"
                    data-keywords="add goods receipt new goods receipt">
                    <i class="bi bi-file-earmark-arrow-down"></i>{{ __('New Goods Receipt') }}
                </a></li>
            <li><a class="dropdown-item" href="{{ route('delivery.order_return_monitoring') }}"
                    data-keywords="order return monitoring">
                    <i class="bi bi-cart-check"></i>{{ __('Order/Return Monitoring') }}</a></li>
            <li><a class="dropdown-item" href="{{ route('delivery.request_email') }}"
                    data-keywords="order return email">
                    <i class="bi bi-envelope"></i>{{ __('Order/Return Email') }}</a></li>
        </ul>
    </li>

    @if (!$isWarehouseYs && !$isWarehousePic)
        {{-- Finance --}}
        <li class="nav-item dropdown" data-roles="admin" data-tour="menu-finance">
            <a class="nav-link dropdown-toggle {{ $isFinance ? 'active' : '' }}" href="#"
                data-bs-toggle="dropdown">
                <i class="bi bi-cash-coin"></i>{{ __('Finance') }}
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('invoice.index') }}" data-keywords="invoice">
                        <i class="bi bi-receipt"></i>{{ __('Invoice') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('usage.index') }}"
                        data-keywords="Warehouse Monthly Usage Check">
                        <i class="bi bi-clipboard-check"></i>{{ __('Monthly Usage') }}</a></li>
            </ul>
        </li>
        {{-- Report --}}
        <li class="nav-item dropdown" data-roles="admin" data-tour="menu-report">
            <a class="nav-link dropdown-toggle {{ $isReports ? 'active' : '' }}" href="#"
                data-bs-toggle="dropdown">
                <i class="bi bi-file-earmark-medical"></i>{{ __('Report') }}
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('report.account_transaction') }}"
                        data-keywords="stock movement report">
                        <i class="bi bi-box-seam"></i>{{ __('Account Transaction') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('report.stock_movement') }}"
                        data-keywords="stock movement report">
                        <i class="bi bi-box-seam"></i>{{ __('Stock Movement') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('report.onhand_inventory') }}"
                        data-keywords="on hand inventory report">
                        <i class="bi bi-postcard"></i>{{ __('On-hand Inventory') }}</a></li>
            </ul>
        </li>
    @endif

    {{-- Management --}}
    <li class="nav-item dropdown" data-roles="admin" data-tour="menu-management">
        <a class="nav-link dropdown-toggle {{ $isManagement ? 'active' : '' }}" href="#"
            data-bs-toggle="dropdown">
            <i class="bi bi-gear"></i>{{ __('Management') }}
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('logisticcompany.index') }}"
                    data-keywords="logistic companies master">
                    <i class="bi bi-buildings"></i>{{ __('Master Logistic Company') }}</a></li>
            @if ($isAdmin)
                <li><a class="dropdown-item" href="{{ route('customers.index') }}" data-keywords="customer master">
                        <i class="bi bi-people"></i>{{ __('Master Customer') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('company.index') }}" data-keywords="company master">
                        <i class="bi bi-building"></i>{{ __('Master Company') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('marketing.index') }}" data-keywords="marketing master">
                        <i class="bi bi-person-badge"></i>{{ __('Master Marketing') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('pallet.index') }}" data-keywords="pallet types master">
                        <i class="bi bi-grid"></i>{{ __('Master Pallet Types') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('vehicle.index') }}" data-keywords="vehicle master">
                        <i class="bi bi-truck"></i>{{ __('Master Vehicle') }}</a></li>
            @endif
        </ul>
    </li>

    {{-- Feedback --}}
    <li class="nav-item">
        <a class="nav-link js-feedback-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}"
            href="{{ route('chatrooms.index') }}" data-keywords="help center support faq glossary guide">
            <i class="bi bi-chat-dots"></i>{{ __('Feedback') }}
        </a>
    </li>

    {{-- Help Center --}}
    <li class="nav-item" data-tour="menu-help">
        <a class="nav-link {{ request()->routeIs('help.*') ? 'active' : '' }}" href="{{ route('help.index') }}"
            data-keywords="help center support faq glossary guide">
            <i class="bi bi-question-circle"></i>{{ __('Help') }}
        </a>
    </li>

    <!-- Hidden search-only menu index (admin only) -->
    <div id="searchIndex" class="d-none">
        @if ($isAdmin)
            <a href="{{ route('customers.create') }}" data-keywords="add customer new customer"
                data-search-only="true">
                New Customer
            </a>

            <a href="{{ route('agreement.create') }}" data-keywords="add agreement new agreement"
                data-search-only="true">
                New Agreement
            </a>

            <a href="{{ route('marketing.create') }}" data-keywords="add marketing new marketing"
                data-search-only="true">
                New Marketing
            </a>
        @endif

        <a href="{{ route('logisticcompany.create') }}" data-keywords="add logistic company new logistic company"
            data-search-only="true">
            New Logistic Company
        </a>
    </div>

</template>

@push('styles')
    <style>
        /* ── Search Box ── */
        .search-box input {
            max-width: 500px;
        }

        /* ── Notification Badge ── */
        .notification-badge {
            font-size: 0.6rem;
            padding: 0.2em 0.4em;
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translate(-25%, -25%);
        }

        /* ── Notification Dropdown Container ── */
        .notification-dropdown {
            width: 380px;
            max-height: 480px;
            overflow-y: auto;
            padding: 0;
        }

        .notification-dropdown .dropdown-header {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            margin: 0;
            position: sticky;
            top: 0;
            z-index: 1;
            font-weight: 600;
            color: #212529;
        }

        .js-feedback-link {
            position: relative;
        }

        .js-feedback-link.feedback-alert {
            color: #dc3545 !important;
        }

        .js-feedback-link.feedback-alert i {
            color: inherit;
        }

        .js-feedback-link.feedback-alert::after {
            content: '';
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background-color: #dc3545;
            border-radius: 50%;
            animation: pulseDot 1.8s infinite;
        }

        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 rgba(220,53,69,0.5); 
            }
            70% {
                box-shadow: 0 0 0 8px rgba(220,53,69,0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(220,53,69,0);
            }
        }

        /* Mark all as read link */
        .notification-action-link {
            font-size: 0.8rem;
            font-weight: 500;
            color: #198754;
            text-decoration: none;
            transition: color 0.15s;
        }

        .notification-action-link:hover {
            color: #146c43;
            text-decoration: underline;
        }

        /* ── Notification Items ── */
        .notification-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            border-left: 3px solid transparent;
            transition: background-color 0.15s;
            white-space: normal;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        /* UNREAD — strong green accent */
        .notification-item.unread {
            background-color: #d9f2e5;
            /* border-left-color: #146c43; */
        }

        .notification-item.unread:hover {
            background-color: #c3ebd6;
        }

        .notification-item.unread .notification-title {
            color: #0a3d22;
            font-weight: 700;
        }

        .notification-item.unread .notification-text {
            color: #1e3a2b;
        }

        /* READ — visually receded but still readable */
        .notification-item.read {
            background-color: #fff;
        }

        .notification-item.read:hover {
            background-color: #f8f9fa;
        }

        .notification-item.read .notification-title {
            color: #495057;
            font-weight: 500;
        }

        .notification-item.read .notification-text {
            color: #6c757d;
        }

        /* ── Notification Content Layout ── */
        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 0.75rem;
            font-size: 1.1rem;
            color: #198754;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .notification-title {
            font-size: 0.875rem;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .notification-time {
            font-size: 0.72rem;
            color: #858a8f;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .notification-text {
            font-size: 0.8125rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ── Mobile ── */
        @media (max-width: 991.98px) {
            .notification-dropdown {
                width: 320px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            'use strict';

            // ========================================================================
            // NAVIGATION INITIALIZATION
            // ========================================================================
            document.addEventListener('DOMContentLoaded', function() {
                initializeSharedMenus();

                setupFeedbackAlertListener();

                const isMobile = window.innerWidth < 992;
                isMobile ? initMobileNav() : initDesktopNav();
            });

            function setupFeedbackAlertListener() {
                if (!window.Echo) {
                    return;
                }

                const chatRoomIds = @json($chatRoomIds);

                if (!Array.isArray(chatRoomIds) || !chatRoomIds.length) {
                    return;
                }

                const setFeedbackAlert = () => {
                    document.querySelectorAll('.js-feedback-link').forEach((feedbackLink) => {
                        feedbackLink.classList.add('feedback-alert');
                    });
                };

                chatRoomIds.forEach((roomId) => {
                    window.Echo.channel(`chatroom.${roomId}`)
                        .listen('.chat.message.sent', setFeedbackAlert);
                });
            }

            // ========================================================================
            // SHARED MENU CLONING
            // ========================================================================
            function initializeSharedMenus() {
                const template = document.getElementById('sharedMenuTemplate');
                const mobileList = document.getElementById('mobileMenuList');
                const desktopList = document.getElementById('desktopMenuList');

                if (template && mobileList && desktopList) {
                    mobileList.innerHTML = template.innerHTML;
                    desktopList.innerHTML = template.innerHTML;
                }
            }

            // ========================================================================
            // MOBILE NAVIGATION
            // ========================================================================
            function initMobileNav() {
                const navbarCollapse = document.getElementById('mobileNav');
                const mobileSearch = document.getElementById('mobileSearch');
                const menuList = document.getElementById('mobileMenuList');

                if (!navbarCollapse) return;

                const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                    toggle: false
                });

                // Close menu on item click
                document.querySelectorAll('#mobileMenuList .dropdown-item').forEach(link => {
                    link.addEventListener('click', () => bsCollapse.hide());
                });

                // Mobile search
                if (mobileSearch && menuList) {
                    let dropdownsOpened = false;

                    mobileSearch.addEventListener('input', debounce(function(e) {
                        const searchTerm = e.target.value.toLowerCase().trim();

                        if (searchTerm === '') {
                            resetMobileSearch(menuList);
                            dropdownsOpened = false;
                            return;
                        }

                        searchMobileMenus(menuList, searchTerm, dropdownsOpened);
                        dropdownsOpened = true;
                    }, 300));
                }
            }

            function resetMobileSearch(menuList) {
                menuList.querySelectorAll('.nav-item').forEach(item => {
                    item.style.display = '';
                    const dropdown = item.querySelector('.dropdown-menu');
                    if (dropdown) {
                        dropdown.querySelectorAll('.dropdown-item, .dropdown-header, .dropdown-divider')
                            .forEach(el => el.style.display = '');

                        const bsDropdown = bootstrap.Dropdown.getInstance(item.querySelector(
                            '.dropdown-toggle'));
                        if (bsDropdown) bsDropdown.hide();
                    }
                });
            }

            function searchMobileMenus(menuList, searchTerm, dropdownsOpened) {
                menuList.querySelectorAll('.nav-item').forEach(item => {
                    let hasVisibleSubItems = false;
                    const dropdown = item.querySelector('.dropdown-menu');

                    if (dropdown) {
                        dropdown.querySelectorAll('.dropdown-item').forEach(subItem => {
                            const keywords = (subItem.getAttribute('data-keywords') || '')
                                .toLowerCase();
                            const text = subItem.textContent.toLowerCase().trim();
                            const matches = (keywords + ' ' + text).split(/\s+/).some(word => word
                                .includes(searchTerm));

                            subItem.style.display = matches ? '' : 'none';
                            if (matches) hasVisibleSubItems = true;
                        });

                        updateDropdownVisibility(dropdown);

                        if (hasVisibleSubItems && !dropdownsOpened) {
                            const dropdownToggle = item.querySelector('.dropdown-toggle');
                            if (dropdownToggle) {
                                new bootstrap.Dropdown(dropdownToggle).show();
                            }
                        } else if (!hasVisibleSubItems) {
                            const bsDropdown = bootstrap.Dropdown.getInstance(item.querySelector(
                                '.dropdown-toggle'));
                            if (bsDropdown) bsDropdown.hide();
                        }
                    }

                    item.style.display = hasVisibleSubItems ? '' : 'none';
                });
            }

            function updateDropdownVisibility(dropdown) {
                const elements = dropdown.querySelectorAll('.dropdown-header, .dropdown-divider, .dropdown-item');

                elements.forEach((el, index) => {
                    if (el.classList.contains('dropdown-header')) {
                        const hasVisibleAfter = Array.from(elements).slice(index + 1)
                            .some(nextEl => !nextEl.classList.contains('dropdown-header') &&
                                !nextEl.classList.contains('dropdown-divider') &&
                                nextEl.style.display !== 'none');
                        el.style.display = hasVisibleAfter ? '' : 'none';
                    } else if (el.classList.contains('dropdown-divider')) {
                        const hasVisibleBefore = Array.from(elements).slice(0, index).reverse()
                            .some(prevEl => prevEl.classList.contains('dropdown-item') && prevEl.style
                                .display !== 'none');
                        const hasVisibleAfter = Array.from(elements).slice(index + 1)
                            .some(nextEl => nextEl.classList.contains('dropdown-item') && nextEl.style
                                .display !== 'none');
                        el.style.display = (hasVisibleBefore && hasVisibleAfter) ? '' : 'none';
                    }
                });
            }

            // ========================================================================
            // DESKTOP NAVIGATION
            // ========================================================================
            function initDesktopNav() {
                const desktopSearch = document.getElementById('desktopSearch');
                const desktopResults = document.getElementById('desktopSearchResults');
                let currentIndex = -1;

                if (desktopSearch && desktopResults) {
                    desktopSearch.addEventListener('input', debounce(e => {
                        performDesktopSearch(e.target.value, desktopResults);
                        currentIndex = -1;
                    }, 250));

                    desktopSearch.addEventListener('keydown', e => {
                        handleSearchKeyboard(e, desktopResults, currentIndex, (newIndex) => {
                            currentIndex = newIndex;
                        });
                    });

                    document.addEventListener('click', e => {
                        if (!e.target.closest('.search-box')) {
                            desktopResults.classList.add('d-none');
                        }
                    });
                }

                initDesktopHover();
            }

            function performDesktopSearch(term, resultsContainer) {
                term = term.toLowerCase().trim();
                resultsContainer.innerHTML = '';

                if (!term) {
                    resultsContainer.classList.add('d-none');
                    return;
                }

                const matches = [];
                const seen = new Set();

                document.querySelectorAll('#desktopMenuList .dropdown-item, #searchIndex a').forEach(item => {
                    const href = item.getAttribute('href');
                    if (!href || seen.has(href)) return;

                    const keywords = (item.dataset.keywords || '').toLowerCase();
                    const text = item.textContent.toLowerCase();
                    const parent = item.closest('.nav-item')?.querySelector('.nav-link')?.textContent.trim() ||
                        (item.dataset.searchOnly === 'true' ? 'Quick Action' : 'Other');

                    if (keywords.includes(term) || text.includes(term)) {
                        seen.add(href);
                        matches.push({
                            text: item.textContent.trim(),
                            parent,
                            href
                        });
                    }
                });

                if (!matches.length) {
                    resultsContainer.innerHTML = '<div class="item text-muted">No results found</div>';
                } else {
                    matches.forEach(m => {
                        const el = document.createElement('div');
                        el.className = 'item';
                        el.innerHTML = `<div>${m.text}</div><small>${m.parent}</small>`;
                        el.addEventListener('click', () => window.location.href = m.href);
                        resultsContainer.appendChild(el);
                    });
                }

                resultsContainer.classList.remove('d-none');
            }

            function handleSearchKeyboard(e, resultsContainer, currentIndex, updateIndex) {
                const items = Array.from(resultsContainer.querySelectorAll('.item'));
                if (!items.length) return;

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        currentIndex = (currentIndex + 1) % items.length;
                        updateIndex(currentIndex);
                        updateActiveItem(items, currentIndex);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        currentIndex = (currentIndex - 1 + items.length) % items.length;
                        updateIndex(currentIndex);
                        updateActiveItem(items, currentIndex);
                        break;
                    case 'Enter':
                        if (currentIndex >= 0) {
                            e.preventDefault();
                            items[currentIndex].click();
                        }
                        break;
                    case 'Escape':
                        resultsContainer.classList.add('d-none');
                        updateIndex(-1);
                        break;
                }
            }

            function updateActiveItem(items, currentIndex) {
                items.forEach((el, i) => {
                    el.classList.toggle('active', i === currentIndex);
                    if (i === currentIndex) {
                        el.scrollIntoView({
                            block: 'nearest'
                        });
                    }
                });
            }

            function initDesktopHover() {
                document.querySelectorAll('#desktopMenuList .dropdown').forEach(dropdown => {
                    let showTimeout, hideTimeout;
                    const toggle = dropdown.querySelector('.dropdown-toggle');

                    dropdown.addEventListener('mouseenter', () => {
                        clearTimeout(hideTimeout);
                        showTimeout = requestAnimationFrame(() => {
                            bootstrap.Dropdown.getOrCreateInstance(toggle).show();
                        });
                    });

                    dropdown.addEventListener('mouseleave', () => {
                        cancelAnimationFrame(showTimeout);
                        hideTimeout = requestAnimationFrame(() => {
                            const instance = bootstrap.Dropdown.getInstance(toggle);
                            if (instance) instance.hide();
                        });
                    });
                });
            }

            // ========================================================================
            // UTILITIES
            // ========================================================================
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }
        })();
    </script>
@endpush
