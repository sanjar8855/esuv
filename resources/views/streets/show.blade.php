@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Ko‘cha Tafsilotlari</h1>
                    <p><strong>Mahalla:</strong> {{ $street->neighborhood->name }}</p>
                    <p><strong>Ko‘cha Nomi:</strong> {{ $street->name }}</p>

                    <a href="{{ route('streets.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
