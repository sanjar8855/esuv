<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'eSuv Admin')</title>

    <!-- Telegram WebApp SDK -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>

    <!-- Tabler CSS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-vendors.min.css') }}">

    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tg-theme-bg-color: #ffffff;
            --tg-theme-text-color: #000000;
            --tg-theme-hint-color: #999999;
            --tg-theme-link-color: #2481cc;
            --tg-theme-button-color: #2481cc;
            --tg-theme-button-text-color: #ffffff;
        }

        body {
            background-color: var(--tg-theme-bg-color);
            color: var(--tg-theme-text-color);
            margin: 0;
            padding: 0;
            padding-bottom: 70px; /* Bottom navigation uchun joy */
            overflow-x: hidden;
        }

        /* Header minimal */
        .webapp-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: var(--tg-theme-bg-color);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .webapp-header h1 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
            color: var(--tg-theme-text-color);
        }

        /* Content area */
        .webapp-content {
            padding: 4rem 1rem 1rem;
            min-height: 100vh;
        }

        /* Bottom Navigation */
        .webapp-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--tg-theme-bg-color);
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0.5rem 0;
            z-index: 1000;
        }

        .webapp-bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            text-decoration: none;
            color: var(--tg-theme-hint-color);
            font-size: 0.75rem;
            transition: color 0.2s;
        }

        .webapp-bottom-nav a.active {
            color: var(--tg-theme-button-color);
        }

        .webapp-bottom-nav a svg {
            width: 24px;
            height: 24px;
            margin-bottom: 0.25rem;
        }

        /* Loading spinner */
        .webapp-loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        /* Hide sidebar elements */
        .navbar-vertical {
            display: none !important;
        }

        /* Full width content */
        .page-wrapper {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        /* Telegram theme colors */
        .btn-primary {
            background-color: var(--tg-theme-button-color) !important;
            color: var(--tg-theme-button-text-color) !important;
            border-color: var(--tg-theme-button-color) !important;
        }

        a {
            color: var(--tg-theme-link-color) !important;
        }

        /* Card optimizations for mobile */
        .card {
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Touch-friendly buttons */
        .btn {
            min-height: 44px;
            padding: 0.75rem 1.25rem;
        }

        /* DataTables responsive */
        .dataTables_wrapper {
            overflow-x: auto;
        }

        table.dataTable {
            font-size: 0.875rem;
        }

        /* Remove navbar from mobile */
        .navbar {
            display: none !important;
        }

        /* Adjust page content */
        .page {
            padding: 0 !important;
        }

        .page-body {
            padding: 0 !important;
        }

        .container-xl {
            padding: 0 !important;
            max-width: 100% !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Loading Spinner -->
    <div id="webapp-loading" class="webapp-loading" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Header -->
    <div class="webapp-header">
        <h1>@yield('page-title', 'eSuv Admin')</h1>
        <div>
            @if(session('is_telegram_webapp'))
                <span class="badge bg-blue-lt">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-telegram" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4"></path>
                    </svg>
                    Telegram
                </span>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="webapp-content">
        <div class="page">
            <div class="page-wrapper">
                <div class="page-body">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="webapp-bottom-nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M5 12l-2 0l9 -9l9 9l-2 0"></path>
                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path>
            </svg>
            <span>Bosh sahifa</span>
        </a>

        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
            </svg>
            <span>Mijozlar</span>
        </a>

        <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"></path>
                <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"></path>
            </svg>
            <span>To'lovlar</span>
        </a>

        <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                <path d="M9 7l1 0"></path>
                <path d="M9 13l6 0"></path>
                <path d="M9 17l6 0"></path>
            </svg>
            <span>Fakturalar</span>
        </a>

        <a href="#" onclick="Telegram.WebApp.close(); return false;">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M9 21h6"></path>
                <path d="M12 18v3"></path>
                <path d="M8 13h8"></path>
                <path d="M12 13v-8"></path>
                <path d="M12 5m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
            </svg>
            <span>Chiqish</span>
        </a>
    </div>

    <!-- Tabler JS -->
    <script src="{{ asset('tabler/js/tabler.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Telegram WebApp Integration -->
    <script>
        // Initialize Telegram WebApp
        window.Telegram.WebApp.ready();
        window.Telegram.WebApp.expand();

        // Apply Telegram theme colors
        const tg = window.Telegram.WebApp;
        if (tg.themeParams) {
            document.documentElement.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color || '#ffffff');
            document.documentElement.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color || '#000000');
            document.documentElement.style.setProperty('--tg-theme-hint-color', tg.themeParams.hint_color || '#999999');
            document.documentElement.style.setProperty('--tg-theme-link-color', tg.themeParams.link_color || '#2481cc');
            document.documentElement.style.setProperty('--tg-theme-button-color', tg.themeParams.button_color || '#2481cc');
            document.documentElement.style.setProperty('--tg-theme-button-text-color', tg.themeParams.button_text_color || '#ffffff');
        }

        // Enable back button
        if (tg.BackButton) {
            tg.BackButton.show();
            tg.BackButton.onClick(function() {
                window.history.back();
            });
        }

        // Show/hide loading spinner
        function showLoading() {
            document.getElementById('webapp-loading').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('webapp-loading').style.display = 'none';
        }

        // Show loading on page navigation
        window.addEventListener('beforeunload', showLoading);
        window.addEventListener('load', hideLoading);

        // CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Haptic feedback for buttons
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A') {
                if (tg.HapticFeedback) {
                    tg.HapticFeedback.impactOccurred('light');
                }
            }
        });

        // Close confirmation
        window.addEventListener('beforeunload', function(e) {
            if (tg.isClosingConfirmationEnabled === false) {
                tg.enableClosingConfirmation();
            }
        });

        console.log('Telegram WebApp initialized:', tg.initDataUnsafe);
    </script>

    @stack('scripts')
</body>
</html>
