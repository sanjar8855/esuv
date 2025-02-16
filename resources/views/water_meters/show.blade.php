@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagich Tafsilotlari</h1>
                    <p><strong>Mijoz:</strong> {{ $waterMeter->customer->name }}</p>
                    <p><strong>Hisoblagich Raqami:</strong> {{ $waterMeter->meter_number }}</p>
                    <p><strong>O‘rnatilgan Sana:</strong> {{ $waterMeter->installation_date ?? 'Noma’lum' }}</p>
                    <p><strong>Oxirgi O‘qish:</strong> {{ $waterMeter->last_reading_date ?? 'Noma’lum' }}</p>
                    <p><strong>Oxirgi ko‘rsatkich:</strong>
                        @if($waterMeter->readings->count())
                            {{ $waterMeter->readings->first()->reading }} ({{ $waterMeter->readings->first()->reading_date }})
                        @else
                            Ko‘rsatkich mavjud emas.
                        @endif
                    </p>

                    <a href="{{ route('water_meters.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
