<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    <!-- Tabler CSS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-flags.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-socials.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-payments.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-vendors.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-marketing.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/demo.min.css') }}">
    <style>
        @import url('https://rsms.me/inter/inter.css');
    </style>
</head>
<body class=" layout-fluid">
<script src="{{ asset('tabler/js/demo-theme.min.js?1738096685') }}"></script>


<div class="page">
    <!-- Sidebar -->
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
                    aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('dashboard') }}">
                    eSuv.uz
                </a>
            </div>
            {{--            <div class="navbar-nav flex-row d-lg-none">--}}
            {{--                <div class="nav-item d-none d-lg-flex me-3">--}}
            {{--                    <div class="btn-list">--}}
            {{--                        <a href="https://github.com/tabler/tabler" class="btn btn-5" target="_blank" rel="noreferrer">--}}
            {{--                            <!-- Download SVG icon from http://tabler.io/icons/icon/brand-github -->--}}
            {{--                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-2"><path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" /></svg>--}}
            {{--                            Source code--}}
            {{--                        </a>--}}
            {{--                        <a href="https://github.com/sponsors/codecalm" class="btn btn-6" target="_blank" rel="noreferrer">--}}
            {{--                            <!-- Download SVG icon from http://tabler.io/icons/icon/heart -->--}}
            {{--                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-pink icon-2"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /></svg>--}}
            {{--                            Sponsor--}}
            {{--                        </a>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--                <div class="d-none d-lg-flex">--}}
            {{--                    <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip"--}}
            {{--                       data-bs-placement="bottom">--}}
            {{--                        <!-- Download SVG icon from http://tabler.io/icons/icon/moon -->--}}
            {{--                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>--}}
            {{--                    </a>--}}
            {{--                    <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip"--}}
            {{--                       data-bs-placement="bottom">--}}
            {{--                        <!-- Download SVG icon from http://tabler.io/icons/icon/sun -->--}}
            {{--                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>--}}
            {{--                    </a>--}}
            {{--                    <div class="nav-item dropdown d-none d-md-flex me-3">--}}
            {{--                        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">--}}
            {{--                            <!-- Download SVG icon from http://tabler.io/icons/icon/bell -->--}}
            {{--                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>--}}
            {{--                            <span class="badge bg-red"></span>--}}
            {{--                        </a>--}}
            {{--                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">--}}
            {{--                            <div class="card">--}}
            {{--                                <div class="card-header">--}}
            {{--                                    <h3 class="card-title">Last updates</h3>--}}
            {{--                                </div>--}}
            {{--                                <div class="list-group list-group-flush list-group-hoverable">--}}
            {{--                                    <div class="list-group-item">--}}
            {{--                                        <div class="row align-items-center">--}}
            {{--                                            <div class="col-auto"><span class="status-dot status-dot-animated bg-red d-block"></span></div>--}}
            {{--                                            <div class="col text-truncate">--}}
            {{--                                                <a href="#" class="text-body d-block">Example 1</a>--}}
            {{--                                                <div class="d-block text-secondary text-truncate mt-n1">--}}
            {{--                                                    Change deprecated html tags to text decoration classes (#29604)--}}
            {{--                                                </div>--}}
            {{--                                            </div>--}}
            {{--                                            <div class="col-auto">--}}
            {{--                                                <a href="#" class="list-group-item-actions">--}}
            {{--                                                    <!-- Download SVG icon from http://tabler.io/icons/icon/star -->--}}
            {{--                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-muted icon-2"><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>--}}
            {{--                                                </a>--}}
            {{--                                            </div>--}}
            {{--                                        </div>--}}
            {{--                                    </div>--}}
            {{--                                    <div class="list-group-item">--}}
            {{--                                        <div class="row align-items-center">--}}
            {{--                                            <div class="col-auto"><span class="status-dot d-block"></span></div>--}}
            {{--                                            <div class="col text-truncate">--}}
            {{--                                                <a href="#" class="text-body d-block">Example 2</a>--}}
            {{--                                                <div class="d-block text-secondary text-truncate mt-n1">--}}
            {{--                                                    justify-content:between ⇒ justify-content:space-between (#29734)--}}
            {{--                                                </div>--}}
            {{--                                            </div>--}}
            {{--                                            <div class="col-auto">--}}
            {{--                                                <a href="#" class="list-group-item-actions show">--}}
            {{--                                                    <!-- Download SVG icon from http://tabler.io/icons/icon/star -->--}}
            {{--                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-yellow icon-2"><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>--}}
            {{--                                                </a>--}}
            {{--                                            </div>--}}
            {{--                                        </div>--}}
            {{--                                    </div>--}}
            {{--                                    <div class="list-group-item">--}}
            {{--                                        <div class="row align-items-center">--}}
            {{--                                            <div class="col-auto"><span class="status-dot d-block"></span></div>--}}
            {{--                                            <div class="col text-truncate">--}}
            {{--                                                <a href="#" class="text-body d-block">Example 3</a>--}}
            {{--                                                <div class="d-block text-secondary text-truncate mt-n1">--}}
            {{--                                                    Update change-version.js (#29736)--}}
            {{--                                                </div>--}}
            {{--                                            </div>--}}
            {{--                                            <div class="col-auto">--}}
            {{--                                                <a href="#" class="list-group-item-actions">--}}
            {{--                                                    <!-- Download SVG icon from http://tabler.io/icons/icon/star -->--}}
            {{--                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-muted icon-2"><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>--}}
            {{--                                                </a>--}}
            {{--                                            </div>--}}
            {{--                                        </div>--}}
            {{--                                    </div>--}}
            {{--                                    <div class="list-group-item">--}}
            {{--                                        <div class="row align-items-center">--}}
            {{--                                            <div class="col-auto"><span class="status-dot status-dot-animated bg-green d-block"></span></div>--}}
            {{--                                            <div class="col text-truncate">--}}
            {{--                                                <a href="#" class="text-body d-block">Example 4</a>--}}
            {{--                                                <div class="d-block text-secondary text-truncate mt-n1">--}}
            {{--                                                    Regenerate package-lock.json (#29730)--}}
            {{--                                                </div>--}}
            {{--                                            </div>--}}
            {{--                                            <div class="col-auto">--}}
            {{--                                                <a href="#" class="list-group-item-actions">--}}
            {{--                                                    <!-- Download SVG icon from http://tabler.io/icons/icon/star -->--}}
            {{--                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-muted icon-2"><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>--}}
            {{--                                                </a>--}}
            {{--                                            </div>--}}
            {{--                                        </div>--}}
            {{--                                    </div>--}}
            {{--                                </div>--}}
            {{--                            </div>--}}
            {{--                        </div>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--                <div class="nav-item dropdown">--}}
            {{--                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">--}}
            {{--                        <span class="avatar avatar-sm" style="background-image: url({{ asset('tabler/static/avatars/000m.jpg') }})"></span>--}}
            {{--                        <div class="d-none d-xl-block ps-2">--}}
            {{--                            <div>Paweł Kuna</div>--}}
            {{--                            <div class="mt-1 small text-secondary">UI Designer</div>--}}
            {{--                        </div>--}}
            {{--                    </a>--}}
            {{--                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">--}}
            {{--                        <a href="#" class="dropdown-item">Status</a>--}}
            {{--                        <a href="./profile.html" class="dropdown-item">Profile</a>--}}
            {{--                        <a href="#" class="dropdown-item">Feedback</a>--}}
            {{--                        <div class="dropdown-divider"></div>--}}
            {{--                        <a href="./settings.html" class="dropdown-item">Settings</a>--}}
            {{--                        <a href="./sign-in.html" class="dropdown-item">Logout</a>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}

            <div class="collapse navbar-collapse" id="sidebar-menu">
                <ul class="navbar-nav pt-lg-3">
                    @hasrole('admin')
                    @endhasrole
                    @can('dashboard')
                        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Dashboard
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('companies')
                        <li class="nav-item {{ request()->routeIs('companies*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('companies.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Kompaniyalar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('users')
                        <li class="nav-item {{ request()->routeIs('users*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('users.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Xodimlar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('tariffs')
                        <li class="nav-item {{ request()->routeIs('tariffs*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('tariffs.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Tariflar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('customers')
                        <li class="nav-item {{ request()->routeIs('customers*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('customers.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Mijozlar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('water_meters')
                        <li class="nav-item {{ request()->routeIs('water_meters*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('water_meters.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Hisoblagichlar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('meter_readings')
                        <li class="nav-item {{ request()->routeIs('meter_readings*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('meter_readings.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Hisoblagichlar tarixi
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('invoices')
                        <li class="nav-item {{ request()->routeIs('invoices*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('invoices.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Invoyslar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('payments')
                        <li class="nav-item {{ request()->routeIs('payments*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('payments.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                To'lovlar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('notifications')
                        <li class="nav-item {{ request()->routeIs('notifications*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('notifications.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                        d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                        d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Eslatmalar
                            </span>
                            </a>
                        </li>
                    @endcan
                    @can('locations')
                        <li class="nav-item {{ request()->routeIs('regions*') || request()->routeIs('cities*') || request()->routeIs('neighborhoods*') || request()->routeIs('streets*') ? 'active' : '' }} dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-help" data-bs-toggle="dropdown"
                               data-bs-auto-close="false" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/lifebuoy -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="icon icon-1"><path
                                        d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path
                                        d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M15 15l3.35 3.35"/><path
                                        d="M9 15l-3.35 3.35"/><path d="M5.65 5.65l3.35 3.35"/><path
                                        d="M18.35 5.65l-3.35 3.35"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Hududlar
                            </span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item {{ request()->routeIs('regions*') ? 'active' : '' }}"
                                   href="{{ route('regions.index') }}">
                                    Viloyatlar
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('cities*') ? 'active' : '' }}"
                                   href="{{ route('cities.index') }}">
                                    Shaharlar
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('neighborhoods*') ? 'active' : '' }}"
                                   href="{{ route('neighborhoods.index') }}">
                                    Mahallalar
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('streets*') ? 'active' : '' }}"
                                   href="{{ route('streets.index') }}">
                                    Ko'chalar
                                </a>
                            </div>
                        </li>
                    @endcan
                </ul>
            </div>
        </div>
    </aside>

    <div class="page-wrapper">
        <!-- Page header -->
        @include('layouts.navbar')
        <div class="page-body">
            @yield('content')
        </div>

        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center flex-row-reverse">
                    <div class="col-lg-auto ms-lg-auto">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item"><a href="https://tabler.io/docs" target="_blank"
                                                            class="link-secondary" rel="noopener">Documentation</a></li>
                            <li class="list-inline-item"><a href="./license.html" class="link-secondary">License</a>
                            </li>
                            <li class="list-inline-item"><a href="https://github.com/tabler/tabler" target="_blank"
                                                            class="link-secondary" rel="noopener">Source code</a></li>
                            <li class="list-inline-item">
                                <a href="https://github.com/sponsors/codecalm" target="_blank" class="link-secondary"
                                   rel="noopener">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/heart -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon text-pink icon-inline icon-4">
                                        <path
                                            d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"/>
                                    </svg>
                                    Sponsor
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                &copy; 2025
                                <a href="{{route('home')}}" class="link-secondary">eSuv.uz</a>.
                                Barcha huquqlar himoyalangan.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Libs JS -->
<script src="{{ asset('tabler/libs/apexcharts/dist/apexcharts.min.js') }}" defer></script>
<script src="{{ asset('tabler/libs/jsvectormap/dist/jsvectormap.min.js') }}" defer></script>
<script src="{{ asset('tabler/libs/jsvectormap/dist/maps/world.js') }}" defer></script>
<script src="{{ asset('tabler/libs/jsvectormap/dist/maps/world-merc.js') }}" defer></script>
<!-- Tabler Core -->
<script src="{{ asset('tabler/js/tabler.min.js?1738096685') }}"></script>
<script src="{{ asset('tabler/js/demo.min.js?1738096685') }}"></script>

</body>
</html>
