@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Yangi hisob-faktura qo‘shish</h2>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('invoices.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>Mijoz</label>
                            <select name="customer_id" class="form-control">
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Tarif</label>
                            <select name="tariff_id" class="form-control">
                                @foreach($tariffs as $tariff)
                                    <option value="{{ $tariff->id }}">m3={{ $tariff->price_per_m3 }}, 1 inson uchun {{$tariff->for_one_person}}</option>
                                @endforeach
                            </select>
                        </div>
{{--                        <div class="mb-3">--}}
{{--                            <label>Hisob raqami</label>--}}
{{--                            <input type="text" name="invoice_number" class="form-control" value="{{old('invoice_number')}}">--}}
{{--                        </div>--}}
                        <div class="mb-3">
                            <label>Davr (masalan: 2024-02)</label>
                            <input type="text" name="billing_period" class="form-control" value="{{old('billing_period') ?? now()->format('Y-m')}}">
                        </div>
                        <div class="mb-3">
                            <label>Summa</label>
                            <input type="number" name="amount_due" class="form-control" value="{{old('amount_due')}}">
                        </div>
{{--                        <div class="mb-3">--}}
{{--                            <label>To‘lov sanasi</label>--}}
{{--                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->format('Y-m-d')) }}">--}}
{{--                        </div>--}}
                        <div class="mb-3">
                            <label class="form-label">To‘lov sanasi:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </span>
                                <input name="due_date" value="{{ old('due_date', now()->format('Y-m-d')) }}" class="form-control" placeholder="Sanani tanlang" id="datepicker-icon-prepend"/>
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
                                <option value="pending">To‘lanmagan</option>
                                <option value="paid">To‘langan</option>
                                <option value="overdue">Muddati o‘tgan</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>

@endsection
