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
                                    <option value="{{ $tariff->id }}">{{ $tariff->name }}</option>
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
                        <div class="mb-3">
                            <label>To‘lov sanasi</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->format('Y-m-d')) }}">
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
@endsection
