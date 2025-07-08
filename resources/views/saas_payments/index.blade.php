@extends('layouts.app')

{{-- Litepicker kerak bo'lsa, stillarini qo'shish mumkin --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('tabler/libs/litepicker/dist/css/litepicker.css') }}">
@endpush

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1>Kompaniya To'lovlari</h1>
                        <div> {{-- Tugmalarni guruhlash uchun --}}
                            <a href="{{ route('saas.payments.history') }}" class="btn btn-outline-info">To'lovlar Jurnali</a>
                            <a href="{{ route('saas.payments.create') }}" class="btn btn-primary ms-2">Yangi To'lov Qo'shish</a>
                        </div>
                    </div>

                    {{-- ------------- OY BO'YICHA FILTRLASH FORMASI ------------- --}}
                    <div class="card card-body mb-3">
                        <form action="{{ route('saas.payments.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label" for="period-filter">Oy bo'yicha ko'rish:</label>
                                {{-- `name="period"` controllerda qabul qilinadigan nomga mos kelishi kerak --}}
                                <input type="month" name="period" id="period-filter" class="form-control" value="{{ $selectedPeriod }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Ko'rsatish</button>
                            </div>
                        </form>
                    </div>
                    {{-- ------------- FILTR FORMASI TUGADI ------------- --}}


                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            {{-- Sarlavhani tanlangan oyga qarab o'zgartiramiz --}}
                            <h3 class="card-title">{{ \Carbon\Carbon::parse($selectedPeriod)->format('F Y') }} oyi uchun to'lov holati</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>Kompaniya Nomi</th>
                                    <th>Tarif Rejasi</th>
                                    <th>Oylik To'lov (UZS)</th>
                                    <th>Holat</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($companies as $company)
                                    <tr>
                                        <td><a href="{{ route('companies.show', $company->id) }}">{{ $company->name }}</a></td>
                                        <td>{{ $company->plan->name ?? 'Belgilanmagan' }}</td>
                                        <td>{{ number_format($company->plan->price ?? 0, 0, '.', ' ') }}</td>
                                        <td>
                                            @if($company->is_paid_for_selected_month) {{-- O'zgaruvchi nomi yangilandi --}}
                                            <span class="badge bg-green text-green-fg">To'langan</span>
                                            @else
                                                <span class="badge bg-red text-red-fg">Qarzdor</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('saas.payments.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-outline-success">
                                                To'lov qo'shish
                                            </a>
                                            {{-- <a href="#" class="btn btn-sm btn-info">Tarix</a> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Kompaniyalar mavjud emas.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- JavaScript yordamida oy tanlanganda avtomatik sahifani yangilash (ixtiyoriy) --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const periodFilter = document.getElementById('period-filter');
            if (periodFilter) {
                periodFilter.addEventListener('change', function () {
                    this.form.submit(); // Oy tanlanganda formani avtomatik yuborish
                });
            }
        });
    </script>
@endpush
