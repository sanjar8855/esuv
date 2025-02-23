@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>To‘lovlar Ro‘yxati</h1>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary mb-3">Yangi To‘lov Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Mijoz</th>
                                    <th>Invoice</th>
                                    <th>Miqdori</th>
                                    <th>To‘lov usuli</th>
                                    <th>Sana</th>
                                    <th>Harakatlar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{$loop->index +1}}</td>
                                        <td>
                                            @if ($payment->customer)
                                                <a href="{{ route('customers.show', $payment->customer->id) }}"
                                                   class="badge badge-outline text-blue">
                                                    {{ $payment->customer->name }}
                                                </a>
                                            @else
                                                <span class="badge badge-outline text-danger">Mijoz yo‘q</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($payment->invoice)
                                                {{ $payment->invoice->invoice_number }}
                                            @else
                                                <span class="badge badge-outline text-danger">Invoice yo‘q</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->amount }} UZS</td>
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
                                        <td>{{ $payment->payment_date }}</td>
                                        <td>
                                            <a href="{{ route('payments.show', $payment->id) }}"
                                               class="btn btn-info btn-sm">Ko'rish</a>
                                            <a href="{{ route('payments.edit', $payment->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            <form action="{{ route('payments.destroy', $payment->id) }}" method="POST"
                                                  style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
