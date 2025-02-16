@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Mahalla Tafsilotlari</h1>
                    <p><strong>Shahar:</strong> {{ $neighborhood->city->name }}</p>
                    <p><strong>Mahalla Nomi:</strong> {{ $neighborhood->name }}</p>

                    <a href="{{ route('neighborhoods.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
