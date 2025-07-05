@extends('layouts.app')

{{-- TomSelect va Litepicker CSS fayllarini @push orqali qo'shamiz --}}
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('tabler/libs/litepicker/dist/css/litepicker.css') }}">
@endpush

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <form action="{{ route('saas.payments.store') }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Yangi Kompaniya To'lovini Qo'shish</h4>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <strong>Xatoliklar mavjud:</strong>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label required" for="company_id">Kompaniya</label>
                                    <select name="company_id" id="company-select" class="form-select @error('company_id') is-invalid @enderror" required>
                                        <option value="">Kompaniyani tanlang...</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                    data-plan-price="{{ $company->plan->price ?? 0 }}"
                                                    {{ (isset($selectedCompanyId) && $selectedCompanyId == $company->id) || old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }} ({{ $company->plan->name ?? 'Tarifsiz' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required" for="payment_period">To'lov Qaysi Oy Uchun</label>
                                    <input type="text" name="payment_period" id="payment-period-picker" class="form-control @error('payment_period') is-invalid @enderror"
                                           value="{{ old('payment_period', now()->format('Y-m')) }}" required>
                                    @error('payment_period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required" for="amount">To'lov Summasi (UZS)</label>
                                    <input type="number" name="amount" id="amount-input" class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" required min="0" step="any">
                                    <small class="form-hint">Kompaniya tanlanganda, shu yerga uning tarif narxi avtomatik qo'yiladi.</small>
                                    @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required" for="payment_date">To'lov Sanasi</label>
                                    <input type="text" name="payment_date" id="payment-date-picker" class="form-control @error('payment_date') is-invalid @enderror"
                                           value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                                    @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="payment_method">To'lov Usuli</label>
                                    <select name="payment_method" id="payment_method" class="form-select">
                                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Bank O'tkazmasi</option>
                                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Karta Orqali</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Naqd Pul</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="notes">Izohlar</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                </div>

                            </div>
                            <div class="card-footer text-end">
                                <a href="{{ route('saas.payments.index') }}" class="btn">Bekor qilish</a>
                                <button type="submit" class="btn btn-primary">To'lovni Saqlash</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- TomSelect va Litepicker JS fayllari --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Kompaniya tanlash menyusi uchun TomSelect
            let companySelect = new TomSelect('#company-select', {
                create: false,
                sortField: { field: "text", direction: "asc" }
            });

            // To'lov summasini avtomatik to'ldirish
            let amountInput = document.getElementById('amount-input');
            companySelect.on('change', function(value){
                if(value) {
                    let selectedOption = this.options[value];
                    let price = selectedOption.dataset.planPrice;
                    amountInput.value = parseFloat(price).toFixed(0);
                } else {
                    amountInput.value = '';
                }
            });
            // Agar sahifa xatolik bilan qayta yuklansa, avvalgi qiymatni tiklash uchun
            if(companySelect.getValue()) {
                let selectedOption = companySelect.options[companySelect.getValue()];
                if(amountInput.value === '') { // Agar summa qo'lda o'zgartirilmagan bo'lsa
                    amountInput.value = parseFloat(selectedOption.dataset.planPrice).toFixed(0);
                }
            }

            // To'lov sanasi uchun Litepicker
            if (window.Litepicker) {
                new Litepicker({ element: document.getElementById('payment-date-picker'), format: 'YYYY-MM-DD', autoApply: true, dropdowns: {months: true, years: true} });
                new Litepicker({ element: document.getElementById('payment-period-picker'), format: 'YYYY-MM', autoApply: true, dropdowns: {months: true, years: true} });
            }
        });
    </script>
@endpush