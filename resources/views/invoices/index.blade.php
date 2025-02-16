@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Hisob-fakturalar</h2>
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary mb-3">Yangi hisob-faktura</a>
                    <table class="table table-bordered">
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
                                        <a href="{{ route('customers.show', $invoice->customer->id) }}" class="badge badge-outline text-blue">
                                            {{ $invoice->customer->name }}
                                        </a>
                                    @else
                                        <span class="badge badge-outline text-danger">Mijoz yo‘q</span>
                                    @endif
                                </td>
                                <td>{{ $invoice->tariff->name }}</td>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->billing_period }}</td>
                                <td>{{ number_format($invoice->amount_due, 2) }} UZS</td>
                                <td>{{ ucfirst($invoice->status) }}</td>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-info btn-sm">Ko‘rish</a>
                                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning btn-sm">Tahrirlash</a>
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
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
