@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
        <h1>Hisoblagich O‘qish Tafsilotlari</h1>
        <p><strong>Mijoz:</strong> {{ $meterReading->waterMeter->customer->name }}</p>
        <p><strong>Hisoblagich:</strong> {{ $meterReading->waterMeter->meter_number }}</p>
        <p><strong>O‘qish:</strong> {{ $meterReading->reading }}</p>
        <p><strong>O‘qish sanasi:</strong> {{ $meterReading->reading_date }}</p>
        <p><strong>Holat:</strong> {{ $meterReading->confirmed ? 'Tasdiqlangan' : 'Tasdiqlanmagan' }}</p>

        <a href="{{ route('meter_readings.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
