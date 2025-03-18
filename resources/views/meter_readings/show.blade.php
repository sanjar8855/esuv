@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagich O‘qish Tafsilotlari</h1>
                    <p><strong>Mijoz:</strong>
                        <a href="{{ route('customers.show', $meterReading->waterMeter->customer->id) }}"
                           class="badge badge-outline text-blue">
                            {{ $meterReading->waterMeter->customer->name }}
                        </a>
                    </p>
                    <p><strong>Hisoblagich:</strong> {{ number_format($meterReading->waterMeter->meter_number, 0, '.', ' ') }}</p>
                    <p><strong>O‘qish:</strong> {{ number_format($meterReading->reading, 0, '.', ' ') }}</p>
                    <p><strong>O‘qish sanasi:</strong> {{ $meterReading->reading_date }}</p>
                    <p><strong>Holat:</strong> {{ $meterReading->confirmed ? 'Tasdiqlangan' : 'Tasdiqlanmagan' }}</p>

                    <a href="{{ route('meter_readings.index') }}" class="btn btn-secondary">Ortga</a>
                    <a href="{{ route('meter_readings.edit', $meterReading->id) }}"
                       class="btn btn-warning">Tahrirlash</a>
                    <form action="{{ route('meter_readings.destroy', $meterReading->id) }}"
                          method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">O‘chirish</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
