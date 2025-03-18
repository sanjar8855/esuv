@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Tarif Tafsilotlari</h2>

                    <div class="card">
                        <div class="card-body">
{{--                            <h4 class="card-title">{{ $tariff->name }}</h4>--}}
                            <h4>1m³ narxi: {{ number_format($tariff->price_per_m3, 0, '.', ' ') }} UZS</h4>
                            <h4>Meyoriy bir kishiga: {{ number_format($tariff->for_one_person, 0, '.', ' ') }} UZS</h4>
                            <p><strong>Kompaniya:</strong> {{ $tariff->company->name }}</p>
                            <p><strong>Boshlanish sanasi:</strong> {{ $tariff->valid_from }}</p>
                            <p><strong>Tugash sanasi:</strong> {{ $tariff->valid_to ?? 'Cheklanmagan' }}</p>
                            <p><strong>Holati:</strong> {{ $tariff->is_active ? 'Aktiv' : 'Nofaol' }}</p>
                            <a href="{{ route('tariffs.edit', $tariff->id) }}" class="btn btn-warning">Tahrirlash</a>
                            <a href="{{ route('tariffs.index') }}" class="btn btn-secondary">Ortga</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
