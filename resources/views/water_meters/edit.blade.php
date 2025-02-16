@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagichni Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('water_meters.update', $waterMeter->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="customer_id">Mijoz tanlang:</label>
                            <select name="customer_id" class="form-control" required>
                                @foreach($customers as $customer)
                                    <option
                                        value="{{ $customer->id }}" {{ $waterMeter->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="meter_number">Hisoblagich Raqami:</label>
                            <input type="number" name="meter_number" class="form-control"
                                   value="{{ $waterMeter->meter_number }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="installation_date">O‘rnatilgan Sana:</label>
                            <input type="date" name="installation_date" class="form-control"
                                   value="{{ $waterMeter->installation_date }}">
                        </div>

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
