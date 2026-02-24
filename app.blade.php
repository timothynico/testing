<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png" />
    <title>@yield('title', config('app.name', 'Yanarental'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    {{-- Font Awesome Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- FancyBox --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

    {{-- Flatpickr (formats date input dd/mm/yyyy instead of mm/dd/yyyy) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    {{-- Driver Js --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 12px;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f5f5f5;
            color: #212529;
            font-size: 0.875rem;
        }

        .form-check-label {
            font-size: 0.9rem;
        }

        /* Readonly input styling */
        input[readonly],
        textarea[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 0.8;
        }

        /* Optional: Add a subtle border color for readonly fields */
        input[readonly]:focus,
        textarea[readonly]:focus {
            background-color: #e9ecef;
            border-color: #ced4da;
            box-shadow: none;
        }

        .btn-warning {
            background-color: #FFA500;
            /* Orange color */
            border-color: #FFA500;
            /* Optional: also change the border color */
            color: #FFFFFF;
            /* White text color */
        }

        /* Optional: change the hover/focus state as well */
        .btn-warning:hover,
        .btn-warning:focus {
            background-color: #FF8C00;
            /* Darker orange on hover */
            border-color: #FF8C00;
            color: #FFFFFF;
        }

        /* Select2 Styling - Fixed */
        .select2-container--default .select2-selection--single {
            height: 31px;
            display: flex;
            align-items: center;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #212529;
            line-height: 28px;
            padding-left: 8px;
            padding-right: 20px;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #212529 !important;
            line-height: 28px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 31px;
            top: 0;
            right: 1px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-top: -2px;
        }

        /* Remove any conflicting rules */
        .select2-selection__rendered {
            line-height: 28px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #86FEB7;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        /* Navbar */
        .navbar {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0;
            min-height: 50px;
            position: relative;
            z-index: 1030;
        }

        .navbar-brand {
            padding: 0.5rem 1rem 0.5rem 0;
            margin: 0 1rem 0 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
            border-right: 1px solid #dee2e6;
        }

        .navbar-brand:hover {
            color: #198754;
        }

        .navbar-brand img {
            height: 24px;
            width: auto;
        }

        .navbar-toggler {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border: none;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-nav {
            gap: 0;
        }

        .navbar .dropdown-menu {
            margin-top: 0;
        }

        .nav-link {
            color: #495057;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 0.875rem !important;
            transition: all 0.15s;
            border-bottom: 2px solid transparent;
            display: flex;
            align-items: center;
        }

        /* Hover */
        .navbar .nav-link:hover {
            color: #198754;
        }

        /* Active page */
        .navbar .nav-link.active {
            color: #198754;
            border-bottom-color: #198754;
        }

        /* Open dropdown */
        .navbar .nav-link.dropdown-toggle.show {
            color: #198754;
        }

        .nav-link i,
        .dropdown-item i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        @media (min-width: 992px) {

            .navbar .nav-link:focus,
            .navbar .nav-link:focus-visible,
            .navbar .dropdown-toggle:focus {
                outline: none !important;
                box-shadow: none !important;
            }
        }

        /* Dropdowns */
        .dropdown-menu {
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 0.25rem 0;
            font-size: 0.875rem;
            margin-top: 0;
        }

        .dropdown-header {
            padding: 0.375rem 0.875rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dropdown-item {
            padding: 0.5rem 0.875rem;
            font-size: 0.95rem;
            color: #495057;
            transition: all 0.15s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #198754;
        }

        .dropdown-item:active {
            background-color: #198754;
            color: #fff;
        }

        .dropdown-divider {
            margin: 0.25rem 0;
        }

        /* User Avatar */
        .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        }

        /* Search Box */
        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            padding: 0.5rem 0.75rem 0.5rem 2.25rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            font-size: 0.95rem;
            width: 100%;
            max-width: 500px;
            height: 38px;
            line-height: 1.5;
        }

        .search-box input::placeholder {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .search-box input:focus {
            border-color: #198754;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 0.75rem;
            color: #6c757d;
            pointer-events: none;
            z-index: 1;
            font-size: 1rem;
        }

        /* Mobile search box */
        .search-box-mobile {
            position: relative;
            width: calc(100% - 2rem);
            margin: 0.5rem 1rem 1rem 1rem;
        }

        .search-box-mobile input {
            padding: 0.5rem 0.75rem 0.5rem 2.25rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            font-size: 0.95rem;
            width: 100%;
            height: 38px;
            line-height: normal;
        }

        .search-box-mobile input::placeholder {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .search-box-mobile input:focus {
            border-color: #198754;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }

        .search-box-mobile i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
            z-index: 1;
            font-size: 1rem;
        }

        .search-suggestions .item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .search-suggestions .item.active {
            background-color: #e9f5ef;
            /* adjust to your green theme */
        }

        /* Page Layout */
        .page-header {
            position: sticky;
            top: 0;
            z-index: 1010;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(6px);
            background-color: rgba(255, 255, 255, 0.96);
        }

        .sidebar-sticky {
            top: calc(var(--page-header-height) + 1rem);
            z-index: 1000;
            /* lower than page header */
        }

        main {
            padding: 1rem 0;
        }

        /* Cards */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .stat-card {
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Brand Utilities */
        .text-brand {
            color: #198754;
        }

        .bg-brand {
            background-color: #198754;
        }

        .btn-brand {
            background-color: #198754;
            border-color: #198754;
            color: white;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        .btn-brand:hover {
            background-color: #157347;
            border-color: #146c43;
            color: white;
        }

        .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        .btn-sm {
            font-size: 0.8125rem;
            padding: 0.375rem 0.75rem;
        }

        .mobile-username {
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.95rem;
        }

        .search-suggestions {
            position: absolute;
            top: 42px;
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .12);
            z-index: 1050;
            max-height: 320px;
            overflow-y: auto;
        }

        .search-suggestions .item {
            padding: 0.6rem 0.75rem;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .search-suggestions .item:hover {
            background-color: #f8f9fa;
        }

        .search-suggestions .item small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        /* Container */
        .container {
            max-width: 100%;
        }

        /* Master Header Breadcrumb for Create/Update Pages */
        .breadcrumb {
            background: none;
            padding: 0;
            margin-top: 0.25rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
        }

        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: #198754;
        }

        /* Pills Card Header Action Buttons */
        .header-action {
            background: #ffffff;
            color: #198754;
            border: 1px solid #198754;
            border-radius: 999px;

            font-size: 0.72rem;
            font-weight: 600;

            padding: 3px 9px;
            line-height: 1;

            display: inline-flex;
            align-items: center;
            gap: 4px;

            cursor: pointer;
            transition: background-color 0.15s ease,
                color 0.15s ease,
                box-shadow 0.15s ease,
                transform 0.1s ease;
        }

        .header-action i {
            font-size: 0.7rem;
        }

        .header-action:hover {
            background: #198754;
            color: #ffffff;
            box-shadow: 0 1px 4px rgba(25, 135, 84, 0.25);
        }

        .header-action:active {
            transform: translateY(1px);
            box-shadow: none;
        }

        /* Green toggle switch */
        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }

        .form-check-input:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        /* Add consistent borders to all form inputs */
        .form-control,
        .form-select {
            border: 1px solid #ced4da;
        }

        .form-select-sm,
        .form-control-sm {
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }

        .form-label.small {
            font-size: 0.8125rem;
            margin-bottom: 0.25rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86FEB7;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        /* Input Styling */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #86FEB7;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        .input-group .form-control,
        .input-group .form-select {
            border: 1px solid #ced4da;
        }

        .input-group .form-control:focus,
        .input-group .form-select:focus {
            border-color: #86b7fe;
            z-index: 3;
        }

        /* Fix input-group height consistency */
        .input-group-sm>.form-control,
        .input-group-sm>.form-select {
            min-height: calc(1.5em + 0.75rem + 2px);
        }

        .input-group-sm .form-select {
            padding-top: 0.375rem;
            padding-bottom: 0.375rem;
        }

        #serverTime,
        #localDateTime {
            font-variant-numeric: tabular-nums;
            font-family: 'Courier New', monospace;
        }

        @media (min-width: 1400px) {
            .container {
                max-width: 1320px;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .navbar-brand {
                border-right: none;
                margin: 0 auto;
            }

            .navbar-brand img {
                height: 22px;
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .navbar-nav {
                padding: 0;
            }

            .nav-link {
                border-bottom: none;
                border-left: 3px solid transparent;
                padding: 0.75rem 1rem !important;
            }

            .nav-link.active {
                border-left-color: #198754;
                background-color: #f8f9fa;
                border-bottom-color: transparent;
            }

            .offcanvas {
                width: 280px;
                transition: transform 0.25s ease-out;
            }

            .offcanvas-backdrop {
                position: fixed;
                inset: 0;
                /* top, right, bottom, left = 0 */
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                pointer-events: auto;
            }

            body.menu-open {
                overflow: hidden;
            }

            @media (prefers-reduced-motion: reduce) {

                .navbar-collapse,
                .navbar-collapse.collapsing {
                    transition: none !important;
                }
            }
        }

        /* App Footer */
        .app-footer {
            border-top: 1px solid #dee2e6;
            background-color: #f5f5f5;
            font-size: 0.75rem;
            color: #6c757d;
            padding: 0.5rem 0;
        }

        .app-footer strong {
            color: #212529;
        }

        @media (max-width: 575.98px) {
            .app-footer .container-fluid {
                text-align: center;
                gap: 0.25rem;
                flex-direction: column;
            }
        }

        /* Mobile offcanvas width */
        @media (max-width: 575.98px) {
            .offcanvas-start {
                width: 260px;
            }
        }

        /* Small tablets / large phones */
        @media (min-width: 576px) and (max-width: 991.98px) {
            .offcanvas-start {
                width: 300px;
            }
        }

        /* Driver.js Custom Theme - Match Your Brand */
        :root {
            --driver-overlay-color: rgba(0, 0, 0, 0.5);
            --driver-active-element-color: rgba(25, 135, 84, 0.1);
            --driver-popover-color: #ffffff;
            --driver-text-color: #212529;
            --driver-btn-color: #198754;
            --driver-btn-text-color: #ffffff;
        }

        .driver-popover {
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .driver-popover-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #198754;
        }

        .driver-popover-description {
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .driver-popover-next-btn,
        .driver-popover-prev-btn {
            background-color: #198754 !important;
            color: white !important;
            border: none !important;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .driver-popover-next-btn:hover,
        .driver-popover-prev-btn:hover {
            background-color: #146c43 !important;
        }

        .driver-popover-close-btn {
            color: #6c757d !important;
        }

        .driver-popover-close-btn:hover {
            color: #212529 !important;
        }

        /* Tour popover icon styling */
        .driver-popover-description .bi {
            color: #198754;
            font-size: 0.9rem;
        }

        .driver-popover-description .badge {
            font-size: 0.7rem;
            padding: 0.25em 0.5em;
        }

        .driver-active-element {
            scroll-margin-top: 90px;
        }

        /* Tour Pulse Animation for Clickable Elements */
        @keyframes tour-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }

        .tour-pulse-animation {
            animation: tour-pulse 1.5s infinite;
            position: relative;
            z-index: 1000;
        }

        /* Make modal appear above tour overlay */
        .modal.show {
            z-index: 10000 !important;
        }

        .modal-backdrop.show {
            z-index: 9999 !important;
        }

        /* Ensure driver.js popover appears above modal */
        .driver-popover-wrapper {
            z-index: 10001 !important;
        }

        /* Fix Driver.js button double / blurry text */
        .driver-popover-next-btn,
        .driver-popover-prev-btn,
        .driver-popover-close-btn {
            text-shadow: none !important;
        }
    </style>

    @livewireStyles
    @vite(['resources/js/app.js'])
</head>

<body>
    <div class="d-flex flex-column min-vh-100">
        @include('layouts.navigation')

        @hasSection('header-left')
            <header class="page-header">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center justify-content-between">
                        @yield('header-left')

                        <div class="d-flex align-items-center gap-3">
                            {{-- ⭐ ADD THIS TOUR BUTTON --}}
                            <button type="button" id="startTourBtn"
                                class="btn btn-sm btn-success d-flex align-items-center gap-1"
                                style="font-size: 0.8rem; padding: 0.25rem 0.75rem;">
                                <i class="bi bi-magic" style="font-size: 0.9rem;"></i>
                                <span class="d-none d-md-inline">{{ __('Take Tour') }}</span>
                            </button>

                            <div class="text-end lh-sm flex-shrink-0">
                                <div class="fw-semibold small">
                                    <span class="badge bg-light text-dark me-2">Server</span>
                                    <span id="serverTime">{{ now()->format('d/m/y H:i:s') }}</span>
                                </div>
                                <div class="fw-semibold small">
                                    <span class="badge bg-light text-dark me-2" id="localZone">Local</span>
                                    <span id="localDateTime"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        @endif

        <!-- Main Content -->
        <main class="flex-grow-1">
            <div class="container-fluid px-lg-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>{{ __('Please fix the following errors:') }}</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (isset($slot))
                    {{ $slot }}        {{-- untuk Livewire full page --}}
                @else
                    @yield('content') {{-- untuk Blade biasa --}}
                @endif
            </div>
        </main>

        <footer class="app-footer">
            <div class="container-fluid px-3 px-lg-4 d-flex justify-content-between align-items-center">
                <div>
                    © {{ date('Y') }} - <strong>PT Yanasurya Bhaktipersada</strong>
                </div>
                <div>
                    {{ config('app.name') }}
                </div>
            </div>
        </footer>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (must be before Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Global UI Initializer -->
    <script>
        $(document).ready(function() {
            $('.searchable-select').each(function() {
                // Skip province and city - they have custom logic
                if ($(this).attr('id') === 'provinsi' || $(this).attr('id') === 'kota') {
                    return; // skip this iteration
                }

                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        width: '100%'
                    });
                }
            });
        });
    </script>

    {{-- In app.blade.php, in the existing script section --}}
    <script>
        // ============================================================================
        // GLOBAL TOUR UTILITIES
        // ============================================================================
        window.tourUtils = {
            // Highlight and open Network dropdown
            highlightNetworkMenu: function(driver) {
                console.log('Attempting to highlight Network menu');

                // Try multiple selectors to find the Network dropdown
                const selectors = [
                    '.nav-item.dropdown[data-roles="admin"]:has(a:contains("Network"))',
                    '.nav-item.dropdown .nav-link:contains("Network")',
                    '#desktopMenuList .nav-item.dropdown:first-child',
                    '.navbar-nav .dropdown:has(.bi-diagram-3)'
                ];

                let networkDropdown = null;

                for (const selector of selectors) {
                    try {
                        networkDropdown = document.querySelector(selector);
                        if (networkDropdown) {
                            console.log('Found network dropdown with selector:', selector);
                            break;
                        }
                    } catch (e) {
                        // Selector might not be valid, continue
                    }
                }

                // Fallback: Find by icon
                if (!networkDropdown) {
                    const allDropdowns = document.querySelectorAll('.nav-item.dropdown');
                    allDropdowns.forEach(dropdown => {
                        const icon = dropdown.querySelector('.bi-diagram-3');
                        if (icon) {
                            networkDropdown = dropdown;
                            console.log('Found network dropdown by icon');
                        }
                    });
                }

                if (!networkDropdown) {
                    console.error('Could not find Network dropdown');
                    alert('Navigation menu not found. Please refresh the page and try again.');
                    return null;
                }

                // Open the dropdown
                const dropdownToggle = networkDropdown.querySelector('.dropdown-toggle');
                if (dropdownToggle && window.innerWidth >= 992) {
                    const bsDropdown = bootstrap.Dropdown.getOrCreateInstance(dropdownToggle);
                    bsDropdown.show();
                }

                return networkDropdown;
            },

            // Highlight Agreements link
            highlightAgreementLink: function() {
                console.log('Attempting to highlight Agreement link');

                const selectors = [
                    'a[href*="agreement"][data-keywords*="agreement"]',
                    '.dropdown-menu a[href*="agreement"]',
                    'a.dropdown-item:contains("Agreements")'
                ];

                let agreementLink = null;

                for (const selector of selectors) {
                    try {
                        agreementLink = document.querySelector(selector);
                        if (agreementLink && agreementLink.offsetParent !== null) { // Check if visible
                            console.log('Found agreement link with selector:', selector);
                            break;
                        }
                    } catch (e) {
                        // Continue
                    }
                }

                // Fallback: find by text content
                if (!agreementLink) {
                    const allLinks = document.querySelectorAll('.dropdown-menu a');
                    allLinks.forEach(link => {
                        if (link.textContent.trim().includes('Agreement')) {
                            agreementLink = link;
                            console.log('Found agreement link by text content');
                        }
                    });
                }

                if (agreementLink) {
                    agreementLink.classList.add('tour-pulse-animation');
                } else {
                    console.error('Could not find Agreement link');
                }

                return agreementLink;
            }
        };
    </script>

    {{-- Format DateTime and Ticking --}}
    <script>
        function pad(n) {
            return n.toString().padStart(2, '0');
        }

        function formatDateTime(date) {
            return pad(date.getDate()) + '/' +
                pad(date.getMonth() + 1) + '/' +
                date.getFullYear().toString().slice(-2) + ' ' +
                pad(date.getHours()) + ':' +
                pad(date.getMinutes()) + ':' +
                pad(date.getSeconds());
        }

        function formatDateTimeUTC7(date) {
            // Convert to UTC+7 by getting UTC time and adding 7 hours
            const utc7Date = new Date(date.getTime() + (7 * 60 * 60 * 1000));
            return pad(utc7Date.getUTCDate()) + '/' +
                pad(utc7Date.getUTCMonth() + 1) + '/' +
                utc7Date.getUTCFullYear().toString().slice(-2) + ' ' +
                pad(utc7Date.getUTCHours()) + ':' +
                pad(utc7Date.getUTCMinutes()) + ':' +
                pad(utc7Date.getUTCSeconds());
        }

        function updateTimes() {
            const now = new Date();

            // Update Server Time (UTC+7) - synced with actual current UTC+7 time
            const serverTimeEl = document.getElementById('serverTime');
            if (serverTimeEl) {
                serverTimeEl.textContent = formatDateTimeUTC7(now);
            }

            // Update Local Time
            const localTimeEl = document.getElementById('localDateTime');
            if (localTimeEl) {
                localTimeEl.textContent = formatDateTime(now);
            }

            // Update Local Timezone Badge
            const zoneEl = document.getElementById('localZone');
            if (zoneEl) {
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone ||
                    'Local'; //dipake kalau mau munculin ex : Asia/Bangkok nya
                const offset = -now.getTimezoneOffset() / 60;
                const offsetStr = offset >= 0 ? `+${offset}` : `${offset}`;
                zoneEl.textContent = `Local · (UTC${offsetStr})`;
            }
        }

        // Initial update
        updateTimes();

        // Update every second
        setInterval(updateTimes, 1000);
    </script>

    {{-- Driver.js --}}
    <script src="https://unpkg.com/driver.js@1.3.1/dist/driver.js.iife.js"></script>

    {{-- Global Tour Button Handler --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Tour button script loaded');

            const tourBtn = document.getElementById('startTourBtn');
            console.log('Tour button found:', tourBtn !== null);

            if (tourBtn) {
                tourBtn.addEventListener('click', function() {
                    console.log('Tour button clicked');
                    console.log('Driver.js available:', typeof window.driver !== 'undefined');
                    console.log('startProductTour available:', typeof window.startProductTour !==
                        'undefined');

                    if (typeof window.startProductTour === 'function') {
                        window.startProductTour();
                    } else {
                        alert('No tour available for this page.');
                        console.error('window.startProductTour is not defined');
                    }
                });
            }
        });
    </script>

    @livewireScripts
    @stack('styles')
    @stack('scripts')
</body>

</html>
