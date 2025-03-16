@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Hisob-fakturalar, {{$invoicesCount}} ta</h2>
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary mb-3">Yangi hisob-faktura</a>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Mijoz</th>
                                    <th>Tarif</th>
                                    <th>Hisob raqami</th>
                                    <th>Davr</th>
                                    <th>Summa</th>
                                    <th>Holat</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td>{{$loop->index +1}}</td>

                                        <td>
                                            @if ($invoice->customer)
                                                <a href="{{ route('customers.show', $invoice->customer->id) }}"
                                                   class="badge badge-outline text-blue">
                                                    {{ $invoice->customer->name }}
                                                </a>
                                            @else
                                                <span class="badge badge-outline text-danger">Mijoz yo‘q</span>
                                            @endif
                                        </td>
                                        <td>m3: {{ $invoice->tariff->price_per_m3 }}, 1 inson uchun: {{$invoice->tariff->for_one_person}}</td>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->billing_period }}</td>
                                        <td>{{ number_format($invoice->amount_due, 2) }} UZS</td>
                                        <td>
                                            @if($invoice->status == 'pending')
                                                <span class="badge bg-yellow text-yellow-fg">To'liq to‘lanmagan</span>
                                            @elseif($invoice->status == 'paid')
                                                <span class="badge bg-green text-green-fg">To‘langan</span>
                                            @elseif($invoice->status == 'overdue')
                                                <span class="badge bg-red text-red-fg">Muddati o‘tgan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice->id) }}"
                                               class="btn btn-info btn-sm">Ko‘rish</a>
                                            <a href="{{ route('invoices.edit', $invoice->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
