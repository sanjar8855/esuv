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
                            <select name="customer_id" class="form-control">
                                @foreach($customers as $customer)
                                    <option
                                        value="{{ $customer->id }}" {{ $invoice->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Tarif</label>
                            <select name="tariff_id" class="form-control">
                                @foreach($tariffs as $tariff)
                                    <option
                                        value="{{ $tariff->id }}" {{ $invoice->tariff_id == $tariff->id ? 'selected' : '' }}>
                                        {{ $tariff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
{{--                        <div class="mb-3">--}}
{{--                            <label>Hisob raqami</label>--}}
{{--                            <input type="text" name="invoice_number" class="form-control"--}}
{{--                                   value="{{ $invoice->invoice_number }}">--}}
{{--                        </div>--}}
                        <div class="mb-3">
                            <label>Davr (masalan: 2024-02)</label>
                            <input type="text" name="billing_period" class="form-control"
                                   value="{{ $invoice->billing_period }}">
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
