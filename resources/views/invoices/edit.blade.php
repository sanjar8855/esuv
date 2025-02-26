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
                        <div class="mb-3">
                            <label>To‘lov muddati</label>
                            <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date }}">
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
@endsection
