@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
        <h1>Foydalanuvchi Tafsilotlari</h1>
        <p><strong>Kompaniya:</strong> {{ $user->company->name ?? 'Belgilanmagan' }}</p>
        <p><strong>Ism:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>

        <a href="{{ route('users.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
