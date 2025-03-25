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

            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Invoyslar va to'lovlar</div>
                        </div>
                        <div id="chart-invoices-payments" class="chart-sm"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 qarzdor ko‘chalar</h3>
                    </div>
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th>Ko‘cha</th>
                            <th>Qarzdorlik (UZS)</th>
                            <th class="w-50">Ulushi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($topStreets as $street)
                            @php
                                $percent = $maxDebt > 0 ? round(($street['total_debt'] / $maxDebt) * 100, 2) : 0;
                            @endphp
                            <tr>
                                <td>{{ $street['street_name'] }}</td>
                                <td>{{ number_format($street['total_debt'], 0, '', ' ') }}</td>
                                <td class="w-50">
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-red" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-md-6">
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
