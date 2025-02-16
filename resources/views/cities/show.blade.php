@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Shahar Tafsilotlari</h1>
                    <p><strong>Viloyat:</strong> {{ $city->region->name }}</p>
                    <p><strong>Shahar Nomi:</strong> {{ $city->name }}</p>

                    <a href="{{ route('cities.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
