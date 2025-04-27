@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-4">
                    <h2>{{ $company->name }} kompaniyasi</h2>

                    <table class="table">
                        <tr>
                            <th>Nomi:</th>
                            <td>{{ $company->name }}</td>
                        </tr>
{{--                        <tr>--}}
{{--                            <th>Email:</th>--}}
{{--                            <td>{{ $company->email }}</td>--}}
{{--                        </tr>--}}
                        <tr>
                            <th>Telefon:</th>
                            <td>{{ $company->phone }}</td>
                        </tr>
                        <tr>
                            <th>Plan:</th>
                            <td>{{ $company->plan }}</td>
                        </tr>
                        <tr>
                            <th>Manzil:</th>
                            <td>{{ $company->address ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>Hisob raqam:</th>
                            <td>{{ $company->schet ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>INN:</th>
                            <td>{{ $company->inn ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>Izoh:</th>
                            <td>{{ $company->description ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>Logo:</th>
                            <td>
                                <img src="{{ asset('tabler/img/hero/'.$company->logo) }}" alt="">
                            </td>
                        </tr>
                        <tr>
                            <th>Holati:</th>
                            <td>
                                @if($company->is_active)
                                    <span class="badge bg-cyan text-cyan-fg">Faol</span>
                                @else
                                    <span class="badge bg-red  text-red-fg">Faol emas</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <a href="{{ route('companies.index') }}" class="btn btn-secondary">Orqaga</a>
                    <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-warning">Tahrirlash</a>

                    <form action="{{ route('companies.destroy', $company->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Haqiqatan ham o‘chirilsinmi?')">O‘chirish
                        </button>
                    </form>
                </div>
                <div class="col-4">
                    <h2>Tariflar</h2>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>Nomi</th>
                                    <th>1 m³ narxi</th>
                                    <th>1 kishiga narx</th>
                                    <th>Holati</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($company->tariffs as $tariff)
                                    <tr>
                                        <td>{{ $tariff->name }}</td>
                                        <td>{{ number_format($tariff->price_per_m3, 0, '.', ' ') }} so'm</td>
                                        <td>{{ number_format($tariff->for_one_person, 0, '.', ' ') }} so'm</td>
                                        <td>
                                            @if($tariff->is_active)
                                                <span class="badge bg-cyan text-cyan-fg">Faol</span>
                                            @else
                                                <span class="badge bg-red text-red-fg">Faol emas</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tariflar mavjud emas</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <h2>Xodimlar</h2>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>F.I.O</th>
                                    <th>Lavozim</th>
                                    <th>Ish boshlagan sana</th>
                                    <th>Rol</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($company->users as $user)
                                    <tr>
                                        <td>
                                            <a href="{{route('users.show', $user->id)}}" class="badge badge-outline text-blue">{{ $user->name }}</a>
                                        </td>
                                        <td>{{ $user->rank }}</td>
                                        <td>{{ $user->work_start ? date('d.m.Y', strtotime($user->work_start)) : 'Ko\'rsatilmagan' }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-blue text-blue-fg">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Xodimlar mavjud emas</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
