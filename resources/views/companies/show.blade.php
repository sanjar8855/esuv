@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12 col-md-6">
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
                            <th>1 m³ narxi:</th>
                            <td>{{ number_format($tariff->price_per_m3, 0, '.', ' ') }} so'm</td>
                        </tr>
                        <tr>
                            <th>1 kishiga narx:</th>
                            <td>{{ number_format($tariff->for_one_person, 0, '.', ' ') }} so'm</td>
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

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Mijozlar (Operatorlar Bo'yicha)</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>Operator</th>
                                    <th class="text-end">Mijozlar Soni</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($finalOperatorStats as $operatorName => $count)
                                    <tr>
                                        <td>{{ $operatorName }}</td>
                                        <td class="text-end">{{ $count }} ta</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Telefon raqamlari bo'yicha ma'lumot mavjud emas</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <h2>Xodimlar</h2>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>F.I.O</th>
                                    <th>Lavozim</th>
                                    {{--                                    <th>Ish boshlagan sana</th>--}}
                                    <th>Rol</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($company->users as $user)
                                    <tr>
                                        <td>
                                            <a href="{{route('users.show', $user->id)}}"
                                               class="badge badge-outline text-blue">{{ $user->name }}</a>
                                        </td>
                                        <td>{{ $user->rank }}</td>
                                        {{--                                        <td>{{ $user->work_start ? date('d.m.Y', strtotime($user->work_start)) : 'Ko\'rsatilmagan' }}</td>--}}
                                        <td>
                                            @foreach($user->roles as $role)
                                                @switch($role->name)
                                                    @case('company_owner')
                                                    <span class="badge bg-green text-green-fg">Boshqaruv</span>
                                                    @break
                                                    @case('employee')
                                                    <span
                                                        class="badge bg-secondary text-secondary-fg">Ishchi xodim</span>
                                                    @break
                                                @endswitch
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

                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Biriktirilgan Mahallalar</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mahalla Nomi</th>
                                    <th>Shahar/Tuman</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($neighborhoods as $neighborhood)
                                    <tr>
                                        <td>{{ $neighborhood->id }}</td>
                                        <td>
                                            <a href="{{ route('neighborhoods.show', $neighborhood->id) }}"
                                               class="badge badge-outline text-blue">
                                                {{ $neighborhood->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('cities.show', $neighborhood->city->id) }}"
                                               class="badge badge-outline text-blue">
                                                {{ $neighborhood->city->name }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Bu kompaniyaga mahallalar biriktirilmagan.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($neighborhoods->hasPages())
                            <div class="card-footer d-flex align-items-center">
                                {{ $neighborhoods->links() }} {{-- Pagination --}}
                            </div>
                        @endif
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Biriktirilgan Ko'chalar</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ko'cha Nomi</th>
                                    <th>Mahalla</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($streets as $street)
                                    <tr>
                                        <td>{{ $street->id }}</td>
                                        <td>
                                            <a href="{{ route('streets.show', $street->id) }}"
                                               class="badge badge-outline text-blue">
                                                {{ $street->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('neighborhoods.show', $street->neighborhood->id) }}"
                                               class="badge badge-outline text-blue">
                                                {{ $street->neighborhood->name }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Bu kompaniyaga ko'chalar biriktirilmagan.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($streets->hasPages())
                            <div class="card-footer d-flex align-items-center">
                                {{ $streets->links() }} {{-- Pagination --}}
                            </div>
                        @endif
                    </div>

                </div>

                <div class="col-12 mt-4"> {{-- Sahifaning to'liq eni bo'yicha, yuqoridan joy tashlab --}}
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kompaniya To'lovlari Tarixi</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>To'lov Sanasi</th>
                                    <th>To'lov Davri (Oy)</th>
                                    <th>Summa (UZS)</th>
                                    <th>Usul</th>
                                    <th>Izohlar</th>
                                    <th>Kim Qo'shdi</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($saasPayments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y') }}</td>
                                        <td>{{ $payment->payment_period }}</td>
                                        <td>{{ number_format($payment->amount, 0, '.', ' ') }}</td>
                                        <td>
                                            {{-- Agar metodlar standart bo'lsa, ularni tarjima qilish mumkin --}}
                                            @switch($payment->payment_method)
                                                @case('cash') Naqd pul @break
                                                @case('card') Karta orqali @break
                                                @case('transfer') Bank o'tkazmasi @break
                                                @default {{ $payment->payment_method }}
                                            @endswitch
                                        </td>
                                        <td>{{ $payment->notes }}</td>
                                        <td>
                                            @if($payment->createdBy)
                                                <a href="{{ route('users.show', $payment->createdBy->id) }}">
                                                    {{ $payment->createdBy->name }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Bu linklar saas.payments resource route'iga ishora qiladi --}}
                                            <a href="{{ route('saas.payments.edit', $payment->id) }}" class="btn btn-sm btn-warning">Tahrirlash</a>
                                            <form action="{{ route('saas.payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Haqiqatan ham o‘chirmoqchimisiz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Bu kompaniya uchun hali to'lovlar kiritilmagan.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- Pagination linklari --}}
                        @if ($saasPayments->hasPages())
                            <div class="card-footer">
                                {{ $saasPayments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
