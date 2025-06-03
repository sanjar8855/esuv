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

        /* Logotiplar uchun standart holat */
        .navbar-brand .logo-compact {
            display: none;
        }
        .navbar-brand .logo-full {
            /* Tabler standartiga qarab block yoki inline-block bo'lishi mumkin */
            display: inline-block;
        }

        /* Katta ekranlar uchun (>= 992px) */
        @media (min-width: 992px) {
            /* ---- Yon menyu Kengliklari ---- */
            aside.navbar-vertical {
                /* To'liq menyu kengligini bu yerda belgilamaymiz, Tabler o'zi qo'ysin */
                /* Agar Tabler kenglikni bermasa, avvalgi kabi 250px yoki haqiqiy qiymatni qo'ying */
                /* width: 250px !important; */
                transition: width 0.2s ease-in-out;
            }
            aside.navbar-vertical.sidebar-compact {
                width: 72px !important; /* Ixcham menyu kengligi */
                overflow: hidden;
            }

            /* ---- Ixcham Menyuning Ichki Ko'rinishi (logotip, matnlar, ikonka) ---- */
            /* Bu qismlar avvalgi javobdagidek qoladi */
            aside.navbar-vertical.sidebar-compact .navbar-brand {
                padding-left: 0 !important;
                padding-right: 0 !important;
                justify-content: center;
            }
            aside.navbar-vertical.sidebar-compact .navbar-brand .logo-full {
                display: none !important;
            }
            aside.navbar-vertical.sidebar-compact .navbar-brand .logo-compact {
                display: block !important;
                margin: 0 auto !important;
            }
            aside.navbar-vertical.sidebar-compact .nav-link-title {
                display: none !important;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.1s ease-in-out, visibility 0.1s ease-in-out;
            }
            aside.navbar-vertical.sidebar-compact .nav-link {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 0.5rem !important;
            }
            aside.navbar-vertical.sidebar-compact .nav-link .nav-link-icon {
                margin-right: 0 !important;
            }

            .page-wrapper {
                /* To'liq menyu holati uchun padding-left ni bu yerda belgilamaymiz. */
                /* Tabler o'zining standart stillari bilan buni to'g'ri qilishi kerak. */
                transition: padding-left 0.2s ease-in-out, margin-left 0.2s ease-in-out; /* margin-left ni ham kuzatamiz */
            }

            /* Faqat menyu ixchamlashganda .page-wrapper uchun paddingni o'rnatamiz */
            .page.page-sidebar-compacted .page-wrapper {
                padding-left: 72px !important;
                margin-left: 0 !important;
            }
            #desktop-sidebar-compact-toggle svg{
                margin-top:0px;
                margin-bottom:0px;
            }
            .navbar-collapse .navbar-nav .nav-item .nav-link .nav-link-icon{
                margin-left:10px;
            }
        }

        /* Kichik ekranlar uchun (< 992px) */
        @media (max-width: 991.98px) {
            #desktop-sidebar-compact-toggle { display: none !important; }
            .page .page-wrapper { padding-left: 0; margin-left: 0; }
            .page.page-sidebar-compacted .page-wrapper { padding-left: 0; margin-left: 0; }
            /* ... mobil uchun ixcham menyu stillarini bekor qilish ... */
        }
    </style>

</head>
<body class=" layout-fluid">
<script src="{{ asset('tabler/js/demo-theme.min.js?1738096685') }}"></script>


<div class="page">
    <!-- Sidebar -->
    <button class="btn btn-icon d-none d-lg-block" type="button" id="desktop-sidebar-compact-toggle"
            style="position: fixed; top: 15px; left: 15px; z-index: 1050; background-color: #206bc4; color:white;"
            aria-label="Yon menyuni ixchamlashtirish">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-menu-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 6l16 0" /><path d="M4 12l16 0" /><path d="M4 18l16 0" /></svg>
    </button>

    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
                    aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon icon-tabler icons-tabler-outline icon-tabler-baseline-density-medium">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M4 20h16"/>
                    <path d="M4 12h16"/>
                    <path d="M4 4h16"/>
                </svg>
            </button>
            <div class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('dashboard') }}">
                    {{--                    eSuv.uz--}}
                    <img src="{{ asset('tabler/img/logo/full-white.png') }}" alt="" width="100" style="margin-top:25px;">
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
                                     fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-chart-pie-2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 3v9h9"/>
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                                </svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-users-group"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                        d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path
                                        d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"/><path
                                        d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M17 10h2a2 2 0 0 1 2 2v1"/><path
                                        d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path
                                        d="M3 13v-1a2 2 0 0 1 2 -2h2"/></svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-news"><path stroke="none"
                                                                                                          d="M0 0h24v24H0z"
                                                                                                          fill="none"/><path
                                        d="M16 6h3a1 1 0 0 1 1 1v11a2 2 0 0 1 -4 0v-13a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1v12a3 3 0 0 0 3 3h11"/><path
                                        d="M8 8l4 0"/><path d="M8 12l4 0"/><path d="M8 16l4 0"/></svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-user-dollar"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                        d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h3"/><path
                                        d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path
                                        d="M19 21v1m0 -8v1"/></svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-dashboard"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                        d="M12 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path
                                        d="M13.45 11.55l2.05 -2.05"/><path d="M6.4 20a9 9 0 1 1 11.2 0z"/></svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-droplet-plus"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                        d="M18.602 12.004a6.66 6.66 0 0 0 -.538 -1.127l-4.89 -7.26c-.42 -.625 -1.287 -.803 -1.936 -.397a1.376 1.376 0 0 0 -.41 .397l-4.893 7.26c-1.695 2.838 -1.035 6.441 1.567 8.546a7.16 7.16 0 0 0 5.033 1.56"/><path
                                        d="M16 19h6"/><path d="M19 16v6"/></svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-file-dollar"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path
                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path
                                        d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path
                                        d="M12 17v1m0 -8v1"/></svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-currency-dollar"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                        d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path
                                        d="M12 3v3m0 12v3"/></svg>
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
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-mail"><path stroke="none"
                                                                                                          d="M0 0h24v24H0z"
                                                                                                          fill="none"/><path
                                        d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"/><path
                                        d="M3 7l9 6l9 -6"/></svg>
                            </span>
                                <span class="nav-link-title">
                                Xabarnomalar
                            </span>
                            </a>
                        </li>
                    @endcan
                    <li class="nav-item {{ request()->routeIs('streets*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('streets.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13"/><path
                                        d="M13 13h4v8h-10v-6h6"/><path
                                        d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3"/></svg>
                            </span>
                            <span class="nav-link-title">
                                Ko'chalar
                            </span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('neighborhoods*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('neighborhoods.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-building"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0"/><path
                                        d="M9 8l1 0"/><path d="M9 12l1 0"/><path d="M9 16l1 0"/><path d="M14 8l1 0"/><path
                                        d="M14 12l1 0"/><path d="M14 16l1 0"/><path
                                        d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/></svg>
                            </span>
                            <span class="nav-link-title">
                                Mahallalar
                            </span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('cities*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('cities.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-buildings"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                        d="M4 21v-15c0 -1 1 -2 2 -2h5c1 0 2 1 2 2v15"/><path
                                        d="M16 8h2c1 0 2 1 2 2v11"/><path d="M3 21h18"/><path d="M10 12v0"/><path
                                        d="M10 16v0"/><path d="M10 8v0"/><path d="M7 12v0"/><path d="M7 16v0"/><path
                                        d="M7 8v0"/><path d="M17 12v0"/><path d="M17 16v0"/></svg>
                            </span>
                            <span class="nav-link-title">
                                Shaharlar
                            </span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('regions*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('regions.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <!-- Download SVG icon from http://tabler.io/icons/icon/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round"
                                     class="icon icon-tabler icons-tabler-outline icon-tabler-building-skyscraper"><path
                                        stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0"/><path
                                        d="M5 21v-14l8 -4v18"/><path d="M19 21v-10l-6 -4"/><path d="M9 9l0 .01"/><path
                                        d="M9 12l0 .01"/><path d="M9 15l0 .01"/><path d="M9 18l0 .01"/></svg>
                            </span>
                            <span class="nav-link-title">
                                Viloyatlar
                            </span>
                        </a>
                    </li>
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
                            <li class="list-inline-item">
                                Xatolikka duch keldingizmi? telegramdagi
                                <a href="https://t.me/sanjar_asrorov" target="_blank" class="link-secondary"
                                   rel="noopener">@sanjar_asrorov</a> ga murojaat qiling.
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const desktopToggleBtn = document.getElementById('desktop-sidebar-compact-toggle');
        const sidebar = document.querySelector('aside.navbar-vertical');
        const page = document.querySelector('.page'); // Asosiy .page elementi
        const PAGE_SIDEBAR_COMPACTED_CLASS = 'page-sidebar-compacted';
        const SIDEBAR_COMPACT_CLASS = 'sidebar-compact';

        // Menyuning holatini o'rnatadigan funksiya
        function applyCompactState(isCompact) {
            if (!sidebar || !page) return; // Elementlar topilmasa, hech narsa qilmaslik

            if (window.innerWidth >= 992) { // Faqat katta ekranlar uchun
                if (isCompact) {
                    sidebar.classList.add(SIDEBAR_COMPACT_CLASS);
                    page.classList.add(PAGE_SIDEBAR_COMPACTED_CLASS);
                } else {
                    sidebar.classList.remove(SIDEBAR_COMPACT_CLASS);
                    page.classList.remove(PAGE_SIDEBAR_COMPACTED_CLASS);
                }
            } else { // Kichik ekranlarda har doim ixcham bo'lmagan holat
                sidebar.classList.remove(SIDEBAR_COMPACT_CLASS);
                page.classList.remove(PAGE_SIDEBAR_COMPACTED_CLASS);
            }
        }

        if (desktopToggleBtn && sidebar && page) {
            // Sahifa yuklanganda localStorage dan holatni o'qib, o'rnatish
            const savedStateIsCompact = localStorage.getItem('sidebarCompactState') === 'true';
            applyCompactState(savedStateIsCompact);

            desktopToggleBtn.addEventListener('click', function() {
                if (window.innerWidth >= 992) { // Faqat katta ekranlar uchun
                    const currentIsCompact = sidebar.classList.contains(SIDEBAR_COMPACT_CLASS);
                    const newStateIsCompact = !currentIsCompact;

                    applyCompactState(newStateIsCompact);
                    localStorage.setItem('sidebarCompactState', newStateIsCompact ? 'true' : 'false');
                }
            });
        }

        // Ekran o'lchami o'zgarganda menyu holatini to'g'rilash
        window.addEventListener('resize', function() {
            const savedStateIsCompact = localStorage.getItem('sidebarCompactState') === 'true';
            applyCompactState(savedStateIsCompact); // Holatni qayta o'rnatish
        });
    });
</script>

<script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>
<script src="{{ asset('tabler/libs/apexcharts/dist/apexcharts.min.js') }}" defer></script>
<script src="{{ asset('tabler/libs/jsvectormap/dist/jsvectormap.min.js') }}" defer></script>
<script src="{{ asset('tabler/libs/jsvectormap/dist/maps/world.js') }}" defer></script>
<script src="{{ asset('tabler/libs/jsvectormap/dist/maps/world-merc.js') }}" defer></script>
<!-- Tabler Core -->
<script src="{{ asset('tabler/js/tabler.min.js?1738096685') }}"></script>
<script src="{{ asset('tabler/js/demo.min.js?1738096685') }}"></script>

@stack('scripts')

</body>
</html>
