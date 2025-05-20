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
                    <p>
                        <strong>Hisoblagich:</strong> {{ $meterReading->waterMeter->meter_number }}
                    </p>
                    <p><strong>O‘qish:</strong> {{ number_format($meterReading->reading, 0, '.', ' ') }}</p>
                    <p>
                        <strong>O‘qish sanasi:</strong>
                        {{ $meterReading->created_at ? $meterReading->created_at->setTimezone(config('app.timezone', 'Asia/Tashkent'))->format('d.m.Y H:i') : '-' }}
                    </p>
                    <p><strong>Holat:</strong> {{ $meterReading->confirmed ? 'Tasdiqlangan' : 'Tasdiqlanmagan' }}</p>
                    <p><strong>Yaratgan:</strong> {{ $meterReading->createdBy->name ?? 'Noma’lum' }}</p>
                    <p><strong>Tahrir qilgan:</strong> {{ $meterReading->updatedBy->name ?? 'Noma’lum' }}</p>
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
