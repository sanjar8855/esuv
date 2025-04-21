@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Xodimlar, {{$usersCount}} ta</h1>
                    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Yangi xodim qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ism</th>
                                    <th>Foydalanuvchi turi</th>
                                    <th>Email</th>
                                    <th>Lavozim</th>
                                    <th>Ishga kirgan sana</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-outline text-blue">
                                                    @if($role->name == 'admin')
                                                        Admin
                                                    @elseif($role->name == 'company_owner')
                                                        Direktor
                                                    @elseif($role->name == 'employee')
                                                        Xodim
                                                    @else
                                                        {{-- Agar kutilmagan rol bo'lsa, asl nomini chiqarish --}}
                                                        {{ $role->name }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->rank }}</td>
                                        <td>{{ $user->work_start }}</td>
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}"
                                               class="btn btn-info btn-sm">Batafsil</a>
                                            <a href="{{ route('users.edit', $user->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                  style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
