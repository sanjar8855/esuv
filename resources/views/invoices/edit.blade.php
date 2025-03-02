@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Hisob-fakturani tahrirlash</h2>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label>Mijoz</label>
                            <input type="hidden" name="customer_id" value="{{ $invoice->customer->id }}">
                            <input type="text" name="customer_name" value="{{ $invoice->customer->name }}" disabled class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Tarif</label>
                            <input type="hidden" name="tariff_id" value="{{ $invoice->tariff->id }}">
                            <input type="text" name="tariff_m3" value="m3={{ $invoice->tariff->price_per_m3 }}, 1 inson uchun {{$invoice->tariff->for_one_person}}" disabled class="form-control">
                        </div>
{{--                        <div class="mb-3">--}}
{{--                            <label>Hisob raqami</label>--}}
{{--                            <input type="text" name="invoice_number" class="form-control"--}}
{{--                                   value="{{ $invoice->invoice_number }}">--}}
{{--                        </div>--}}
                        <div class="mb-3">
                            <label>Davr (masalan: 2024-02)</label>
                            <input type="text" name="billing_period" class="form-control" value="{{ $invoice->billing_period }}">
                        </div>
                        <div class="mb-3">
                            <label>Summa</label>
                            <input type="number" name="amount_due" class="form-control"
                                   value="{{ $invoice->amount_due }}">
                        </div>
{{--                        <div class="mb-3">--}}
{{--                            <label>To‘lov sanasi:</label>--}}
{{--                            <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date }}">--}}
{{--                        </div>--}}

                        <div class="mb-3">
                            <label class="form-label">To‘lov sanasi:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </span>
                                <input name="due_date" value="{{ $invoice->due_date }}" class="form-control" placeholder="Sanani tanlang" id="datepicker-icon-prepend"/>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    window.Litepicker && (new Litepicker({
                                        element: document.getElementById('datepicker-icon-prepend'),
                                        format: 'YYYY-MM-DD',
                                        buttonText: {
                                            previousMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
	                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                                            nextMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
	                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                                        },
                                    }));
                                });
                            </script>
                        </div>

                        <div class="mb-3">
                            <label>Holat</label>
                            <select name="status" class="form-control">
                                <option value="pending" {{ $invoice->status == 'pending' ? 'selected' : '' }}>
                                    To‘lanmagan
                                </option>
                                <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>To‘langan
                                </option>
                                <option value="overdue" {{ $invoice->status == 'overdue' ? 'selected' : '' }}>Muddati
                                    o‘tgan
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>

@endsection
