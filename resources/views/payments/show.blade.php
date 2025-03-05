@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>To‘lov tafsilotlari</h1>

                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td>{{ $payment->id }}</td>
                        </tr>
                        <tr>
                            <th>Invoice raqami</th>
                            <td>{{ $payment->invoice->invoice_number ?? 'Noma’lum' }}</td>
                        </tr>
                        <tr>
                            <th>To‘lov miqdori</th>
                            <td>{{ $payment->amount }} UZS</td>
                        </tr>
                        <tr>
                            <th>To‘lov usuli</th>
                            <td>
                                @switch($payment->payment_method)
                                    @case('cash')
                                    Naqd pul
                                    @break
                                    @case('card')
                                    Plastik orqali
                                    @break
                                    @case('transfer')
                                    Bank orqali
                                    @break
                                    @default
                                    Noaniq
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <th>To‘lov sanasi</th>
                            <td>{{ $payment->payment_date }}</td>
                        </tr>
                    </table>

                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">Orqaga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
