@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">

                    {{-- ============ SAHIFA SARLAVHASI ============ --}}
                    <div class="page-header d-print-none mb-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="page-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    Yangi To'lov Qo'shish
                                </h2>
                            </div>
                            <div class="col-auto ms-auto">
                                <a href="{{ route('payments.index') }}" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                    Ortga
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- ============ XATOLAR ============ --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                </div>
                                <div>
                                    <h4 class="alert-title">Xatolar topildi!</h4>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif

                    {{-- ============ FORMA ============ --}}
                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf

                        <div class="card">
                            <div class="card-body">

                                {{-- Mijoz --}}
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label required">Mijoz</label>
                                    <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Tanlang...</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} - {{ $customer->account_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Summa --}}
                                <div class="mb-3">
                                    <label for="amount" class="form-label required">To'lov miqdori (UZS)</label>
                                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" required min="1" step="1">
                                    @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- To'lov usuli --}}
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label required">To'lov usuli</label>
                                    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Tanlang...</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Naqd pul</option>
                                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Plastik karta</option>
                                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Bank o'tkazmasi</option>
                                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Onlayn to'lov</option>
                                    </select>
                                    @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ✅ DIREKTOR UCHUN TASDIQLASH --}}
                                @if(auth()->user()->hasRole('company_owner'))
                                    <div class="mb-3">
                                        <label class="form-label">Tasdiqlanganmi?</label>
                                        <select name="confirmed" class="form-control @error('confirmed') is-invalid @enderror">
                                            <option value="0" {{ old('confirmed') == '0' ? 'selected' : '' }}>❌ Yo'q (Kutilmoqda)</option>
                                            <option value="1" {{ old('confirmed') == '1' ? 'selected' : '' }}>✅ Ha (Tasdiqlangan)</option>
                                        </select>
                                        <small class="form-hint">
                                            Agar "Tasdiqlangan" tanlasangiz, to'lov darhol mijoz balansiga qo'shiladi.
                                        </small>
                                        @error('confirmed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    {{-- ✅ ODDIY XODIM UCHUN --}}
                                    <input type="hidden" name="confirmed" value="0">
                                    <div class="alert alert-info" role="alert">
                                        <div class="d-flex">
                                            <div>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                            </div>
                                            <div>
                                                <h4 class="alert-title">Eslatma</h4>
                                                <div class="text-muted">To'lov direktor tomonidan tasdiqlanishini kutadi.</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                            <div class="card-footer text-end">
                                <a href="{{ route('payments.index') }}" class="btn">Bekor qilish</a>
                                <button type="submit" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"></path><circle cx="12" cy="14" r="2"></circle><polyline points="14 4 14 8 8 8 8 4"></polyline></svg>
                                    Saqlash
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ TOM SELECT ============ --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#customer_id", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Mijozni tanlang...",
                allowEmptyOption: true
            });
        });
    </script>
@endsection
