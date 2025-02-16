@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>To‘lovni Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('payments.update', $payment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Mijoz</label>
                            <input type="hidden" class="form-control" name="customer_id" value="{{ $payment->customer->id }}">
                            <input type="text" class="form-control" value="{{ $payment->customer->name ?? 'Noma’lum' }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="amount">To‘lov miqdori:</label>
                            <input type="number" name="amount" class="form-control" value="{{ $payment->amount }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method">To‘lov usuli:</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash" {{ $payment->payment_method == 'cash' ? 'selected' : '' }}>Naqd
                                </option>
                                <option value="card" {{ $payment->payment_method == 'card' ? 'selected' : '' }}>Karta
                                </option>
                                <option value="transfer" {{ $payment->payment_method == 'transfer' ? 'selected' : '' }}>
                                    Bank o‘tkazmasi
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
