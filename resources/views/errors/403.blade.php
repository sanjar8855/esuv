@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl text-center">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>403 - Ruxsat berilmagan</h1>
                    <p>Siz bu sahifaga kira olmaysiz.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Bosh sahifaga qaytish</a>
                </div>
            </div>
        </div>
    </div>
@endsection
