@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">

            {{-- ============ SAHIFA SARLAVHASI ============ --}}
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Kunlik To'lovlar Hisoboti
                        </h2>
                        <div class="text-muted mt-1">{{ $selectedDate->format('d.m.Y') }} - {{ $selectedDate->locale('uz')->dayName }}</div>
                    </div>
                    <div class="col-auto ms-auto">
                        {{-- ============ SANA TANLASH ============ --}}
                        <form action="{{ route('daily-reports.index') }}" method="GET" class="d-inline-flex">
                            <input type="date" name="date" class="form-control" value="{{ $selectedDate->format('Y-m-d') }}" onchange="this.form.submit()">
                        </form>
                    </div>
                </div>
            </div>

            {{-- ============ STATISTIKA KARTOCHKALARI ============ --}}
            <div class="row row-cards mt-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Jami to'lovlar</div>
                            </div>
                            <div class="h1 mb-0">{{ $stats['total_count'] }} ta</div>
                            <div class="text-muted">{{ number_format($stats['total_amount'], 0, '.', ' ') }} UZS</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Tasdiqlangan</div>
                            </div>
                            <div class="h1 mb-0 text-success">{{ $stats['confirmed_count'] }} ta</div>
                            <div class="text-muted">{{ number_format($stats['confirmed_amount'], 0, '.', ' ') }} UZS</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Kutilmoqda</div>
                            </div>
                            <div class="h1 mb-0 text-warning">{{ $stats['pending_count'] }} ta</div>
                            <div class="text-muted">{{ number_format($stats['pending_amount'], 0, '.', ' ') }} UZS</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Tasdiqlash foizi</div>
                            </div>
                            <div class="h1 mb-0">
                                @if($stats['total_count'] > 0)
                                    {{ round(($stats['confirmed_count'] / $stats['total_count']) * 100) }}%
                                @else
                                    0%
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ TASDIQLANMAGAN TO'LOVLAR (CHECKBOX BILAN) ============ --}}
            @if($pendingPayments->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header bg-warning-lt">
                        <h3 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            Tasdiqlanmagan to'lovlar ({{ $pendingPayments->count() }} ta)
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payments.confirm-multiple') }}" method="POST" id="confirmMultipleForm">
                            @csrf

                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-primary" onclick="selectAll()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                    Hammasini tanlash
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAll()">
                                    Tanlovni bekor qilish
                                </button>
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirmMultiple()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Tanlanganlarni tasdiqlash
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                    <tr>
                                        <th class="w-1">
                                            <input type="checkbox" id="selectAllCheckbox" onclick="toggleAll(this)">
                                        </th>
                                        <th>Mijoz</th>
                                        <th>Summa</th>
                                        <th>To'lov usuli</th>
                                        <th>Kim yaratgan</th>
                                        <th>Yaratilgan vaqt</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($pendingPayments as $payment)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" class="payment-checkbox">
                                            </td>
                                            <td>
                                                <a href="{{ route('customers.show', $payment->customer) }}">
                                                    {{ $payment->customer->name }}<br>
                                                    <small class="text-muted">{{ $payment->customer->account_number }}</small>
                                                </a>
                                            </td>
                                            <td><strong>{{ number_format($payment->amount, 0, '.', ' ') }} UZS</strong></td>
                                            <td>{{ $payment->payment_method_name }}</td>
                                            <td>{{ $payment->createdBy->name ?? 'Noma\'lum' }}</td>
                                            <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- ============ BARCHA TO'LOVLAR ============ --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Barcha to'lovlar</h3>
                </div>
                <div class="card-body">
                    @if($payments->isEmpty())
                        <div class="empty">
                            <p class="empty-title">To'lovlar topilmadi</p>
                            <p class="empty-subtitle text-muted">Tanlangan sana uchun to'lovlar mavjud emas.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>Mijoz</th>
                                    <th>Summa</th>
                                    <th>To'lov usuli</th>
                                    <th>Holat</th>
                                    <th>Kim yaratgan</th>
                                    <th>Tasdiqlangan</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($payments as $payment)
                                    <tr class="{{ !$payment->confirmed ? 'bg-yellow-lt' : '' }}">
                                        <td>
                                            <a href="{{ route('customers.show', $payment->customer) }}">
                                                {{ $payment->customer->name }}<br>
                                                <small class="text-muted">{{ $payment->customer->account_number }}</small>
                                            </a>
                                        </td>
                                        <td><strong>{{ number_format($payment->amount, 0, '.', ' ') }} UZS</strong></td>
                                        <td>{{ $payment->payment_method_name }}</td>
                                        <td>
                                            @if($payment->confirmed)
                                                <span class="badge bg-success">✅ Tasdiqlangan</span>
                                            @else
                                                <span class="badge bg-warning">⏳ Kutilmoqda</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->createdBy->name ?? 'Noma\'lum' }}</td>
                                        <td>
                                            @if($payment->confirmed)
                                                <span class="text-success">
                                                    ✅ {{ $payment->confirmedBy->name ?? 'Admin' }}<br>
                                                    <small class="text-muted">{{ $payment->confirmed_at->format('d.m.Y H:i') }}</small>
                                                </span>
                                            @else
                                                <span class="text-warning">⏳ Tasdiqlanmagan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ============ JAVASCRIPT ============ --}}
    <script>
        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllCheckbox').checked = true;
        }

        function deselectAll() {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
        }

        function confirmMultiple() {
            const checkedBoxes = document.querySelectorAll('.payment-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('❌ Iltimos, kamida bitta to\'lovni tanlang!');
                return false;
            }

            return confirm(`${checkedBoxes.length} ta to'lovni tasdiqlaysizmi?`);
        }
    </script>
@endsection
