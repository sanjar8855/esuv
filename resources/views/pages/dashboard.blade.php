@extends('layouts.app')

@section('title', 'Bosh sahifa')

@section('content')

    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="row row-cards">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                    <span class="bg-primary text-white avatar">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/currency-dollar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                             class="icon icon-1"><path
                                                d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path
                                                d="M12 3v3m0 12v3"/></svg>
                                    </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">
                                            {{$tariff->price_per_m3}} UZS
                                        </div>
                                        <div class="text-secondary">
                                            Faol tarif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                    <span class="bg-facebook text-white avatar">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/shopping-cart -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="icon icon-tabler icons-tabler-outline icon-tabler-user-dollar"><path
                                                stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                                d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path
                                                d="M6 21v-2a4 4 0 0 1 4 -4h3"/><path
                                                d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path
                                                d="M19 21v1m0 -8v1"/></svg>
                                    </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">
                                            {{$customersCount}}
                                        </div>
                                        <div class="text-secondary">
                                            Mijozlar
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                    <span class="bg-red text-white avatar">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/shopping-cart -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="icon icon-tabler icons-tabler-outline icon-tabler-user-dollar"><path
                                                stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                                d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path
                                                d="M6 21v-2a4 4 0 0 1 4 -4h3"/><path
                                                d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path
                                                d="M19 21v1m0 -8v1"/></svg>
                                    </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">
                                            Jami: {{ $debtorsCount }} ta
                                            mijoz, {{ number_format($totalDebt, 0, ',', ' ') }} UZS
                                        </div>
                                        <div class="text-secondary">
                                            Qarzdoz mijozlar
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                    <span class="bg-green text-white avatar">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/shopping-cart -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="icon icon-tabler icons-tabler-outline icon-tabler-user-dollar"><path
                                                stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                                d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path
                                                d="M6 21v-2a4 4 0 0 1 4 -4h3"/><path
                                                d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path
                                                d="M19 21v1m0 -8v1"/></svg>
                                    </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">
                                            Jami: {{ $profitCustomersCount }} ta
                                            mijoz, {{ number_format($totalProfit, 0, ',', ' ') }} UZS
                                        </div>
                                        <div class="text-secondary">
                                            Haqdor mijozlar
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Invoyslar</div>
                        </div>
                        <div id="invoice-bar-chart"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">To'lovlar</div>
                        </div>
                        <div id="payment-bar-chart"></div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Invoyslar va to'lovlar</div>
                        </div>
                        <div id="chart-invoices-payments" class="chart-sm"></div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Hisoblagich ko‘rsatgichlari diagrammasi (Joriy oy)</div>
                        </div>
                        <div id="meter-indicators-chart"></div>
                    </div>
                </div>
            </div>


            <div class="col-lg-6">
                <div class="row row-cards">
                    <div class="col-12">
                        <div class="card" style="height: 28rem">
                            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                                <div class="divide-y">
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                                <span class="avatar avatar-1">JL</span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Jeffie Lewzey</strong> commented on your <strong>"I'm not a
                                                        witch."</strong> post.
                                                </div>
                                                <div class="text-secondary">24 hours ago</div>
                                            </div>
                                            <div class="col-auto align-self-center">
                                                <div class="badge bg-primary"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url('{{ asset('static/avatars/002m.jpg') }}')"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    It's <strong>Mallory Hulme</strong>'s birthday. Wish him well!
                                                </div>
                                                <div class="text-secondary">now</div>
                                            </div>
                                            <div class="col-auto align-self-center">
                                                <div class="badge bg-primary"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url('./static/avatars/003m.jpg')"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Dunn Slane</strong> posted <strong>"Well, what do you
                                                        want?"</strong>.
                                                </div>
                                                <div class="text-secondary">now</div>
                                            </div>
                                            <div class="col-auto align-self-center">
                                                <div class="badge bg-primary"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                                <span class="avatar avatar-1"
                                                      style="background-image: url('./static/avatars/000f.jpg')"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Emmy Levet</strong> created a new project <strong>Morning
                                                        alarm clock</strong>.
                                                </div>
                                                <div class="text-secondary">4 days ago</div>
                                            </div>
                                            <div class="col-auto align-self-center">
                                                <div class="badge bg-primary"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/001f.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Maryjo Lebarree</strong> liked your photo.
                                                </div>
                                                <div class="text-secondary">now</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                                <span class="avatar avatar-1">EP</span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Egan Poetz</strong> registered new client as
                                                    <strong>Trilia</strong>.
                                                </div>
                                                <div class="text-secondary">24 hours ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/002f.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Kellie Skingley</strong> closed a new deal on project
                                                    <strong>Pen Pineapple Apple Pen</strong>.
                                                </div>
                                                <div class="text-secondary">2 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/003f.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Christabel Charlwood</strong> created a new project for
                                                    <strong>Wikibox</strong>.
                                                </div>
                                                <div class="text-secondary">4 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                                <span class="avatar avatar-1">HS</span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Haskel Shelper</strong> change status of <strong>Tabler
                                                        Icons</strong> from <strong>open</strong> to
                                                    <strong>closed</strong>.
                                                </div>
                                                <div class="text-secondary">now</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/006m.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Lorry Mion</strong> liked <strong>Tabler UI Kit</strong>.
                                                </div>
                                                <div class="text-secondary">now</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url('{{ asset('static/avatars/004f.jpg') }}')"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Leesa Beaty</strong> posted new video.
                                                </div>
                                                <div class="text-secondary">2 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url('{{ asset('static/avatars/007m.jpg') }}')"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Perren Keemar</strong> and 3 others followed you.
                                                </div>
                                                <div class="text-secondary">now</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                                <span class="avatar avatar-1">SA</span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Sunny Airey</strong> upload 3 new photos to category
                                                    <strong>Inspirations</strong>.
                                                </div>
                                                <div class="text-secondary">2 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/009m.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Geoffry Flaunders</strong> made a <strong>$10</strong>
                                                    donation.
                                                </div>
                                                <div class="text-secondary">2 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/010m.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Thatcher Keel</strong> created a profile.
                                                </div>
                                                <div class="text-secondary">3 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/005f.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Dyann Escala</strong> hosted the event <strong>Tabler UI
                                                        Birthday</strong>.
                                                </div>
                                                <div class="text-secondary">4 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                            <span class="avatar avatar-1"
                                                  style="background-image: url(./static/avatars/006f.jpg)"></span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Avivah Mugleston</strong> mentioned you on <strong>Best of
                                                        2020</strong>.
                                                </div>
                                                <div class="text-secondary">now</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row">
                                            <div class="col-auto">
                                                <span class="avatar avatar-1">AA</span>
                                            </div>
                                            <div class="col">
                                                <div class="text-truncate">
                                                    <strong>Arlie Armstead</strong> sent a Review Request to <strong>Amanda
                                                        Blake</strong>.
                                                </div>
                                                <div class="text-secondary">2 days ago</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Social Media Traffic</h3>
                    </div>
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th>Network</th>
                            <th colspan="2">Visitors</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Instagram</td>
                            <td>3,550</td>
                            <td class="w-50">
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-primary" style="width: 71%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Twitter</td>
                            <td>1,798</td>
                            <td class="w-50">
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-primary" style="width: 35.96%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Facebook</td>
                            <td>1,245</td>
                            <td class="w-50">
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-primary" style="width: 24.9%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>TikTok</td>
                            <td>986</td>
                            <td class="w-50">
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-primary" style="width: 19.72%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Pinterest</td>
                            <td>854</td>
                            <td class="w-50">
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-primary" style="width: 17.080000000000002%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>VK</td>
                            <td>650</td>
                            <td class="w-50">
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-primary" style="width: 13%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Pinterest</td>
                            <td>420</td>
                            <td class="w-50">
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-primary" style="width: 8.4%"></div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tasks</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <tr>
                                <td class="w-1 pe-0">
                                    <input type="checkbox" class="form-check-input m-0 align-middle"
                                           aria-label="Select task" checked>
                                </td>
                                <td class="w-100">
                                    <a href="#" class="text-reset">Extend the data model.</a>
                                </td>
                                <td class="text-nowrap text-secondary">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/>
                                        <path d="M16 3v4"/>
                                        <path d="M8 3v4"/>
                                        <path d="M4 11h16"/>
                                        <path d="M11 15h1"/>
                                        <path d="M12 15v3"/>
                                    </svg>
                                    December 11, 2024
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/check -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                        2/7
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/message -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M8 9h8"/>
                                            <path d="M8 13h6"/>
                                            <path
                                                d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                                        </svg>
                                        3</a>
                                </td>
                                <td>
                                <span class="avatar avatar-sm"
                                      style="background-image: url(./static/avatars/000m.jpg)"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-1 pe-0">
                                    <input type="checkbox" class="form-check-input m-0 align-middle"
                                           aria-label="Select task">
                                </td>
                                <td class="w-100">
                                    <a href="#" class="text-reset">Verify the event flow.</a>
                                </td>
                                <td class="text-nowrap text-secondary">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/>
                                        <path d="M16 3v4"/>
                                        <path d="M8 3v4"/>
                                        <path d="M4 11h16"/>
                                        <path d="M11 15h1"/>
                                        <path d="M12 15v3"/>
                                    </svg>
                                    October 20, 2024
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/check -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                        0/5
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/message -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M8 9h8"/>
                                            <path d="M8 13h6"/>
                                            <path
                                                d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                                        </svg>
                                        0</a>
                                </td>
                                <td>
                                    <span class="avatar avatar-sm">JL</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-1 pe-0">
                                    <input type="checkbox" class="form-check-input m-0 align-middle"
                                           aria-label="Select task">
                                </td>
                                <td class="w-100">
                                    <a href="#" class="text-reset">Database backup and maintenance</a>
                                </td>
                                <td class="text-nowrap text-secondary">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/>
                                        <path d="M16 3v4"/>
                                        <path d="M8 3v4"/>
                                        <path d="M4 11h16"/>
                                        <path d="M11 15h1"/>
                                        <path d="M12 15v3"/>
                                    </svg>
                                    October 20, 2024
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/check -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                        0/5
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/message -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M8 9h8"/>
                                            <path d="M8 13h6"/>
                                            <path
                                                d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                                        </svg>
                                        0</a>
                                </td>
                                <td>
                                <span class="avatar avatar-sm"
                                      style="background-image: url(./static/avatars/002m.jpg)"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-1 pe-0">
                                    <input type="checkbox" class="form-check-input m-0 align-middle"
                                           aria-label="Select task" checked>
                                </td>
                                <td class="w-100">
                                    <a href="#" class="text-reset">Identify the implementation team.</a>
                                </td>
                                <td class="text-nowrap text-secondary">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/>
                                        <path d="M16 3v4"/>
                                        <path d="M8 3v4"/>
                                        <path d="M4 11h16"/>
                                        <path d="M11 15h1"/>
                                        <path d="M12 15v3"/>
                                    </svg>
                                    January 14, 2025
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/check -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                        6/10
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/message -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M8 9h8"/>
                                            <path d="M8 13h6"/>
                                            <path
                                                d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                                        </svg>
                                        12</a>
                                </td>
                                <td>
                                <span class="avatar avatar-sm"
                                      style="background-image: url(./static/avatars/003m.jpg)"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-1 pe-0">
                                    <input type="checkbox" class="form-check-input m-0 align-middle"
                                           aria-label="Select task">
                                </td>
                                <td class="w-100">
                                    <a href="#" class="text-reset">Define users and workflow</a>
                                </td>
                                <td class="text-nowrap text-secondary">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/>
                                        <path d="M16 3v4"/>
                                        <path d="M8 3v4"/>
                                        <path d="M4 11h16"/>
                                        <path d="M11 15h1"/>
                                        <path d="M12 15v3"/>
                                    </svg>
                                    October 20, 2024
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/check -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                        0/5
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/message -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M8 9h8"/>
                                            <path d="M8 13h6"/>
                                            <path
                                                d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                                        </svg>
                                        0</a>
                                </td>
                                <td>
                                <span class="avatar avatar-sm"
                                      style="background-image: url('./static/avatars/000f.jpg')"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-1 pe-0">
                                    <input type="checkbox" class="form-check-input m-0 align-middle"
                                           aria-label="Select task" checked>
                                </td>
                                <td class="w-100">
                                    <a href="#" class="text-reset">Check Pull Requests</a>
                                </td>
                                <td class="text-nowrap text-secondary">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/>
                                        <path d="M16 3v4"/>
                                        <path d="M8 3v4"/>
                                        <path d="M4 11h16"/>
                                        <path d="M11 15h1"/>
                                        <path d="M12 15v3"/>
                                    </svg>
                                    January 16, 2025
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/check -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                        2/9
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <a href="#" class="text-secondary">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/message -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M8 9h8"/>
                                            <path d="M8 13h6"/>
                                            <path
                                                d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                                        </svg>
                                        3</a>
                                </td>
                                <td>
                                <span class="avatar avatar-sm"
                                      style="background-image: url(./static/avatars/001f.jpg)"></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Invoices</h3>
                    </div>
                    <div class="card-body border-bottom py-3">
                        <div class="d-flex">
                            <div class="text-secondary">
                                Show
                                <div class="mx-2 d-inline-block">
                                    <input type="text" class="form-control form-control-sm" value="8" size="3"
                                           aria-label="Invoices count">
                                </div>
                                entries
                            </div>
                            <div class="ms-auto text-secondary">
                                Search:
                                <div class="ms-2 d-inline-block">
                                    <input type="text" class="form-control form-control-sm" aria-label="Search invoice">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap datatable">
                            <thead>
                            <tr>
                                <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox"
                                                       aria-label="Select all invoices"></th>
                                <th class="w-1">No.
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-up -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-sm icon-thick icon-2">
                                        <path d="M6 15l6 -6l6 6"/>
                                    </svg>
                                </th>
                                <th>Invoice Subject</th>
                                <th>Client</th>
                                <th>VAT No.</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001401</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">Design Works</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-us me-2"></span>
                                    Carlson Limited
                                </td>
                                <td>
                                    87956621
                                </td>
                                <td>
                                    15 Dec 2017
                                </td>
                                <td>
                                    <span class="badge bg-success me-1"></span> Paid
                                </td>
                                <td>$887</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">
                                            Action
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Another action
                                        </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001402</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">UX Wireframes</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-gb me-2"></span>
                                    Adobe
                                </td>
                                <td>
                                    87956421
                                </td>
                                <td>
                                    12 Apr 2017
                                </td>
                                <td>
                                    <span class="badge bg-warning me-1"></span> Pending
                                </td>
                                <td>$1200</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">
                                            Action
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Another action
                                        </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001403</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">New Dashboard</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-de me-2"></span>
                                    Bluewolf
                                </td>
                                <td>
                                    87952621
                                </td>
                                <td>
                                    23 Oct 2017
                                </td>
                                <td>
                                    <span class="badge bg-warning me-1"></span> Pending
                                </td>
                                <td>$534</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">
                                            Action
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Another action
                                        </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001404</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">Landing Page</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-br me-2"></span>
                                    Salesforce
                                </td>
                                <td>
                                    87953421
                                </td>
                                <td>
                                    2 Sep 2017
                                </td>
                                <td>
                                    <span class="badge bg-secondary me-1"></span> Due in 2 Weeks
                                </td>
                                <td>$1500</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">
                                                Action
                                            </a>
                                            <a class="dropdown-item" href="#">
                                                Another action
                                            </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001405</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">Marketing Templates</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-pl me-2"></span>
                                    Printic
                                </td>
                                <td>
                                    87956621
                                </td>
                                <td>
                                    29 Jan 2018
                                </td>
                                <td>
                                    <span class="badge bg-danger me-1"></span> Paid Today
                                </td>
                                <td>$648</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">
                                            Action
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Another action
                                        </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001406</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">Sales Presentation</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-br me-2"></span>
                                    Tabdaq
                                </td>
                                <td>
                                    87956621
                                </td>
                                <td>
                                    4 Feb 2018
                                </td>
                                <td>
                                    <span class="badge bg-secondary me-1"></span> Due in 3 Weeks
                                </td>
                                <td>$300</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">
                                                Action
                                            </a>
                                            <a class="dropdown-item" href="#">
                                                Another action
                                            </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001407</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">Logo & Print</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-us me-2"></span>
                                    Apple
                                </td>
                                <td>
                                    87956621
                                </td>
                                <td>
                                    22 Mar 2018
                                </td>
                                <td>
                                    <span class="badge bg-success me-1"></span> Paid Today
                                </td>
                                <td>$2500</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">
                                                Action
                                            </a>
                                            <a class="dropdown-item" href="#">
                                                Another action
                                            </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td><input class="form-check-input m-0 align-middle" type="checkbox"
                                           aria-label="Select invoice"></td>
                                <td><span class="text-secondary">001408</span></td>
                                <td><a href="invoice.html" class="text-reset" tabindex="-1">Icons</a></td>
                                <td>
                                    <span class="flag flag-xs flag-country-pl me-2"></span>
                                    Tookapic
                                </td>
                                <td>
                                    87956621
                                </td>
                                <td>
                                    13 May 2018
                                </td>
                                <td>
                                    <span class="badge bg-success me-1"></span> Paid Today
                                </td>
                                <td>$940</td>
                                <td class="text-end">
                                <span class="dropdown">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">
                                            Action
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Another action
                                        </a>
                                    </div>
                                </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-secondary">Showing <span>1</span> to <span>8</span> of <span>16</span>
                            entries</p>
                        <ul class="pagination m-0 ms-auto">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path d="M15 6l-6 6l6 6"/>
                                    </svg>
                                    prev
                                </a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item active"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">4</a></li>
                            <li class="page-item"><a class="page-link" href="#">5</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">
                                    next
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1">
                                        <path d="M9 6l6 6l-6 6"/>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // Invoyslar uchun ustun diagramma sozlamalari
            var invoiceOptions = {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,   // Yuklab olish tugmasi
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        export: {
                            csv: {
                                filename: 'Invoices',
                                columnDelimiter: ',',
                                headerCategory: 'Sana',
                                headerValue: 'Invoys summasi'
                            },
                            svg: {filename: 'Invoices'},
                            png: {filename: 'Invoices'}
                        },
                        fontFamily: 'inherit'
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '55%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                series: [{
                    name: 'Invoyslar',
                    data: @json($chartInvoiceData)
                }],
                xaxis: {
                    categories: @json($chartLabels),
                    type: 'datetime',
                    labels: {
                        format: 'yyyy-MM-dd'
                    }
                },
                tooltip: {
                    x: {format: 'yyyy-MM-dd'},
                    theme: 'dark'
                },
                colors: [tabler.getColor("primary")],
                grid: {strokeDashArray: 4},
                legend: {position: 'top'}
            };

            var invoiceChart = new ApexCharts(document.getElementById('invoice-bar-chart'), invoiceOptions);
            invoiceChart.render();

            // Tolovlar uchun ustun diagramma sozlamalari
            var paymentOptions = {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        export: {
                            csv: {
                                filename: 'Payments',
                                columnDelimiter: ',',
                                headerCategory: 'Sana',
                                headerValue: 'Tolov summasi'
                            },
                            svg: {filename: 'Payments'},
                            png: {filename: 'Payments'}
                        },
                        fontFamily: 'inherit'
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '55%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                series: [{
                    name: "To'lovlar",
                    data: @json($chartPaymentData)
                }],
                xaxis: {
                    categories: @json($chartLabels),
                    type: 'datetime',
                    labels: {
                        format: 'yyyy-MM-dd'
                    }
                },
                tooltip: {
                    x: {format: 'yyyy-MM-dd'},
                    theme: 'dark'
                },
                colors: [tabler.getColor("green")],
                grid: {strokeDashArray: 4},
                legend: {position: 'top'}
            };

            var paymentChart = new ApexCharts(document.getElementById('payment-bar-chart'), paymentOptions);
            paymentChart.render();

        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            window.ApexCharts && (new ApexCharts(document.getElementById('chart-invoices-payments'), {
                chart: {
                    type: "line",
                    height: 300,
                    // Sparkline o'chirib, normal to'liq chart ishlatamiz:
                    sparkline: {
                        enabled: false
                    },
                    toolbar: {
                        show: true
                    },
                    animations: {
                        enabled: true
                    },
                    fontFamily: 'inherit'
                },
                stroke: {
                    width: [3, 3],
                    curve: "smooth",
                },
                dataLabels: {
                    enabled: false
                },
                series: [
                    {
                        name: "Invoyslar",
                        data: @json($chartInvoiceData)
                    },
                    {
                        name: "To'lovlar",
                        data: @json($chartPaymentData)
                    },
                ],
                xaxis: {
                    categories: @json($chartLabels), // "2025-03-01", "2025-03-02", ...
                    type: 'datetime',  // apexcharts da tooltipni sana sifatida ko‘rsatish uchun
                },
                tooltip: {
                    x: {
                        format: 'yyyy-MM-dd'
                    },
                    theme: 'dark'
                },
                colors: [
                    tabler.getColor("primary"),
                    tabler.getColor("green") // to'lovlar chizig'i yashil, masalan
                ],
                legend: {
                    position: 'top'
                },
                grid: {
                    strokeDashArray: 4,
                }
            })).render();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var meterChartOptions = {
                chart: {
                    type: 'line',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        export: {
                            csv: {
                                filename: 'MeterIndicators',
                                columnDelimiter: ',',
                                headerCategory: 'Sana',
                                headerValue: 'Hisoblagich ko‘rsatgichlari'
                            },
                            svg: { filename: 'MeterIndicators' },
                            png: { filename: 'MeterIndicators' }
                        },
                        fontFamily: 'inherit'
                    }
                },
                series: [
                    {
                        name: "Tasdiqlangan",
                        data: @json($chartConfirmedData)
                    },
                    {
                        name: "Tasdiqlanmagan",
                        data: @json($chartUnconfirmedData)
                    }
                ],
                xaxis: {
                    categories: @json($chartLabels),
                    type: 'datetime',
                    labels: {
                        format: 'yyyy-MM-dd'
                    }
                },
                tooltip: {
                    x: { format: 'yyyy-MM-dd' },
                    theme: 'dark'
                },
                colors: [tabler.getColor("green"), tabler.getColor("red")],
                grid: { strokeDashArray: 4 },
                legend: { position: 'top' }
            };

            var meterChart = new ApexCharts(document.getElementById('meter-indicators-chart'), meterChartOptions);
            meterChart.render();
        });
    </script>
@endsection
