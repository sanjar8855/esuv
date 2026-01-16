@extends('layouts.app')

@section('title', 'Bosh sahifa')

@section('content')

    <div class="container-xl">
        {{-- ✅ Statistika kartochalari --}}
        <div class="row row-deck row-cards mb-3">
            {{-- Faol tarif --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ number_format($tariff->price_per_m3, 0, '.', ' ') }} UZS</div>
                                <div class="text-secondary">Faol tarif</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mijozlar --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-facebook text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h3"/><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path d="M19 21v1m0 -8v1"/></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ number_format($customersCount) }}</div>
                                <div class="text-secondary">Mijozlar</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Qarzdor mijozlar --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-red text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h3"/><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path d="M19 21v1m0 -8v1"/></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $debtorsCount }} ta / {{ number_format($totalDebt, 0, '.', ' ') }} UZS</div>
                                <div class="text-secondary">Qarzdor mijozlar</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Foyda beruvchi --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h3"/><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/><path d="M19 21v1m0 -8v1"/></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $profitCustomersCount }} ta / {{ number_format($totalProfit, 0, '.', ' ') }} UZS</div>
                                <div class="text-secondary">Foyda beruvchi</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Invoyslar/To'lovlar grafigi (tanlanadigan) --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Oylik statistika</h3>
                        <div class="card-actions">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showInvoices" checked>
                                <span class="form-check-label">Invoyslar</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showPayments" checked>
                                <span class="form-check-label">To'lovlar</span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ MFY (Mahalla) statistikasi --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Mahallalar statistikasi</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-striped">
                            <thead>
                                <tr>
                                    <th>Mahalla nomi</th>
                                    <th class="text-center">Ko'chalar soni</th>
                                    <th class="text-center">Abonentlar soni</th>
                                    <th class="text-end">Jami qarz</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($neighborhoodStats as $stat)
                                    <tr>
                                        <td>{{ $stat['neighborhood_name'] }}</td>
                                        <td class="text-center">{{ $stat['streets_count'] }} ta</td>
                                        <td class="text-center">{{ number_format($stat['customers_count']) }} ta</td>
                                        <td class="text-end">
                                            <span class="badge bg-red text-white">{{ number_format($stat['total_debt'], 0, '.', ' ') }} UZS</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Ma'lumot topilmadi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Barcha ko'chalar ro'yxati (filter va pagination) --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Barcha ko'chalar qarzdorligi</h3>
                    </div>
                    <div class="card-body">
                        {{-- Filter form --}}
                        <form method="GET" action="{{ route('dashboard') }}" class="mb-3">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <select name="neighborhood_id" class="form-select">
                                        <option value="">Barcha mahallalar</option>
                                        @foreach($neighborhoods as $neighborhood)
                                            <option value="{{ $neighborhood->id }}" {{ request('neighborhood_id') == $neighborhood->id ? 'selected' : '' }}>
                                                {{ $neighborhood->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="Ko'cha nomi..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="sort_by" class="form-select">
                                        <option value="total_debt" {{ request('sort_by') == 'total_debt' ? 'selected' : '' }}>Qarzdorlik</option>
                                        <option value="customers_count" {{ request('sort_by') == 'customers_count' ? 'selected' : '' }}>Abonentlar</option>
                                        <option value="street_name" {{ request('sort_by') == 'street_name' ? 'selected' : '' }}>Ko'cha nomi</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="sort_dir" class="form-select">
                                        <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Kamayish</option>
                                        <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>O'sish</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Qidirish</button>
                                </div>
                            </div>
                        </form>

                        {{-- Jadval --}}
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter card-table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ko'cha nomi</th>
                                        <th class="text-center">Abonentlar</th>
                                        <th class="text-end">Jami qarz</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allStreets as $street)
                                        <tr>
                                            <td>{{ $street->neighborhood->name }}, {{ $street->street_name }}</td>
                                            <td class="text-center">{{ number_format($street->customers_count) }} ta</td>
                                            <td class="text-end">
                                                <span class="badge bg-red text-white">{{ number_format($street->total_debt, 0, '.', ' ') }} UZS</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Ma'lumot topilmadi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $allStreets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Chart.js grafigi --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('monthlyChart').getContext('2d');

            const labels = @json($chartLabels);
            const invoicesData = @json($chartInvoiceData);
            const paymentsData = @json($chartPaymentData);

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Invoyslar',
                            data: invoicesData,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.1,
                            hidden: false
                        },
                        {
                            label: 'To\'lovlar',
                            data: paymentsData,
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.1,
                            hidden: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // ✅ Checkbox toggle
            document.getElementById('showInvoices').addEventListener('change', function() {
                chart.data.datasets[0].hidden = !this.checked;
                chart.update();
            });

            document.getElementById('showPayments').addEventListener('change', function() {
                chart.data.datasets[1].hidden = !this.checked;
                chart.update();
            });
        });
    </script>

@endsection
