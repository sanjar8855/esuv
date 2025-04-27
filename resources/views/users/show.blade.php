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
                    <p>
                        <strong>Foydalanuvchi:</strong>
                        @foreach($user->roles as $role)
                            <span class="badge badge-outline text-blue">{{ $role->name }}</span>
                        @endforeach
                    </p>
                    <p><strong>Lavozim:</strong> {{ $user->rank ?? 'Kiritilmagan' }}</p>
                    <p><strong>Ish boshlagan sana:</strong> {{ $user->work_start ?? 'Kiritilmagan' }}</p>

                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
