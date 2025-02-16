@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Hisob-faktura ma’lumotlari</h2>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Hisob-faktura № {{ $invoice->invoice_number }}</h4>
                            <ul class="list-group">
                                <li class="list-group-item"><strong>Mijoz:</strong> {{ $invoice->customer->name }}</li>
                                <li class="list-group-item"><strong>Tarif:</strong> {{ $invoice->tariff->name }}</li>
                                <li class="list-group-item"><strong>Davr:</strong> {{ $invoice->billing_period }}</li>
                                <li class="list-group-item">
                                    <strong>Summa:</strong> {{ number_format($invoice->amount_due, 2) }} UZS
                                </li>
                                <li class="list-group-item"><strong>To‘lov muddati:</strong> {{ $invoice->due_date }}
                                </li>
                                <li class="list-group-item"><strong>Holat:</strong>
                                    @if($invoice->status == 'pending')
                                        <span class="badge bg-warning">To'liq to‘lanmagan</span>
                                    @elseif($invoice->status == 'paid')
                                        <span class="badge bg-success">To‘langan</span>
                                    @elseif($invoice->status == 'overdue')
                                        <span class="badge bg-danger">Muddati o‘tgan</span>
                                    @endif
                                </li>
                            </ul>
                            <div class="mt-3">
                                <a href="{{ route('invoices.edit', $invoice->id) }}"
                                   class="btn btn-warning">Tahrirlash</a>
                                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Orqaga</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
