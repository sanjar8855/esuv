@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $company->name }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%"><small>Nomi:</small></th>
                                        <td><small>{{ $company->name }}</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>Telefon:</small></th>
                                        <td><small>{{ $company->phone }}</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>Plan:</small></th>
                                        <td><small>{{ $company->plan->name }} - {{ number_format($company->plan->price, 0, '.', ' ') }} so'm</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>1 m³ narxi:</small></th>
                                        <td><small>{{ number_format($tariff->price_per_m3, 0, '.', ' ') }} so'm</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>1 kishiga narx:</small></th>
                                        <td><small>{{ number_format($tariff->for_one_person, 0, '.', ' ') }} so'm</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>Manzil:</small></th>
                                        <td><small>{{ $company->address ?? 'Ko`rsatilmagan' }}</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>Hisob raqam:</small></th>
                                        <td><small>{{ $company->schet ?? 'Ko`rsatilmagan' }}</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>INN:</small></th>
                                        <td><small>{{ $company->inn ?? 'Ko`rsatilmagan' }}</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>Izoh:</small></th>
                                        <td><small>{{ $company->description ?? 'Ko`rsatilmagan' }}</small></td>
                                    </tr>
                                    <tr>
                                        <th><small>Logo:</small></th>
                                        <td>
                                            <img src="{{ asset('tabler/img/hero/'.$company->logo) }}" alt="{{ $company->name }}" style="max-width: 100px; height: auto;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><small>Holati:</small></th>
                                        <td>
                                            @if($company->is_active)
                                                <span class="badge bg-success">Faol</span>
                                            @else
                                                <span class="badge bg-danger">Faol emas</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex gap-2">
                                <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary">Orqaga</a>
                                <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-sm btn-warning">Tahrirlash</a>
                                <form action="{{ route('companies.destroy', $company->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Haqiqatan ham o`chirilsinmi?')">O'chirish
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Mijozlar (Operatorlar Bo'yicha)</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>Operator</th>
                                    <th class="text-end">Mijozlar Soni</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($finalOperatorStats as $operatorName => $count)
                                    <tr>
                                        <td><small>{{ $operatorName }}</small></td>
                                        <td class="text-end"><small>{{ $count }} ta</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center"><small>Telefon raqamlari bo'yicha ma'lumot mavjud emas</small></td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Xodimlar ({{ $company->users->count() }} ta)</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>F.I.O</th>
                                    <th>Lavozim</th>
                                    <th>Email</th>
                                    <th>Telefon</th>
                                    <th>Rol</th>
                                    <th class="text-end">Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($company->users as $user)
                                    <tr>
                                        <td>
                                            <a href="{{route('users.show', $user->id)}}" class="text-reset">
                                                <small>{{ $user->name }}</small>
                                            </a>
                                        </td>
                                        <td><small>{{ $user->rank ?? '-' }}</small></td>
                                        <td><small>{{ $user->email }}</small></td>
                                        <td><small>{{ $user->phone ?? '-' }}</small></td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                @switch($role->name)
                                                    @case('company_owner')
                                                    <span class="badge bg-success">Boshqaruv</span>
                                                    @break
                                                    @case('employee')
                                                    <span class="badge bg-secondary">Ishchi xodim</span>
                                                    @break
                                                    @default
                                                    <span class="badge bg-info">{{ $role->name }}</span>
                                                @endswitch
                                            @endforeach
                                        </td>
                                        <td class="text-end">
                                            <a href="{{route('users.show', $user->id)}}" class="btn btn-sm btn-info" title="Batafsil">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler-eye" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" /></svg>
                                            </a>
                                            <a href="{{route('users.edit', $user->id)}}" class="btn btn-sm btn-warning" title="Tahrirlash">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler-edit" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center"><small>Xodimlar mavjud emas</small></td>
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
                            <table class="table table-sm table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th width="10%"><small>ID</small></th>
                                    <th><small>Mahalla Nomi</small></th>
                                    <th><small>Shahar/Tuman</small></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($neighborhoods as $neighborhood)
                                    <tr>
                                        <td><small>{{ $neighborhood->id }}</small></td>
                                        <td>
                                            <a href="{{ route('neighborhoods.show', $neighborhood->id) }}" class="text-reset">
                                                <small>{{ $neighborhood->name }}</small>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('cities.show', $neighborhood->city->id) }}" class="text-reset">
                                                <small>{{ $neighborhood->city->name }}</small>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center"><small>Bu kompaniyaga mahallalar biriktirilmagan.</small></td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($neighborhoods->hasPages())
                            <div class="card-footer d-flex align-items-center">
                                {{ $neighborhoods->links() }}
                            </div>
                        @endif
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Biriktirilgan Ko'chalar</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th width="10%"><small>ID</small></th>
                                    <th><small>Ko'cha Nomi</small></th>
                                    <th><small>Mahalla</small></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($streets as $street)
                                    <tr>
                                        <td><small>{{ $street->id }}</small></td>
                                        <td>
                                            <a href="{{ route('streets.show', $street->id) }}" class="text-reset">
                                                <small>{{ $street->name }}</small>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('neighborhoods.show', $street->neighborhood->id) }}" class="text-reset">
                                                <small>{{ $street->neighborhood->name }}</small>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center"><small>Bu kompaniyaga ko'chalar biriktirilmagan.</small></td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($streets->hasPages())
                            <div class="card-footer d-flex align-items-center">
                                {{ $streets->links() }}
                            </div>
                        @endif
                    </div>

                </div>

                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kompaniya To'lovlari Tarixi</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th><small>To'lov Sanasi</small></th>
                                    <th><small>To'lov Davri</small></th>
                                    <th><small>Summa</small></th>
                                    <th><small>Usul</small></th>
                                    <th><small>Izohlar</small></th>
                                    <th><small>Kim Qo'shdi</small></th>
                                    <th class="text-end"><small>Amallar</small></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($saasPayments as $payment)
                                    <tr>
                                        <td><small>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y') }}</small></td>
                                        <td><small>{{ $payment->payment_period }}</small></td>
                                        <td><small>{{ number_format($payment->amount, 0, '.', ' ') }} UZS</small></td>
                                        <td>
                                            <small>
                                            @switch($payment->payment_method)
                                                @case('cash') Naqd @break
                                                @case('card') Karta @break
                                                @case('transfer') Bank @break
                                                @default {{ $payment->payment_method }}
                                            @endswitch
                                            </small>
                                        </td>
                                        <td><small>{{ $payment->notes ?? '-' }}</small></td>
                                        <td>
                                            <small>
                                            @if($payment->createdBy)
                                                <a href="{{ route('users.show', $payment->createdBy->id) }}" class="text-reset">
                                                    {{ $payment->createdBy->name }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('saas.payments.edit', $payment->id) }}" class="btn btn-sm btn-warning" title="Tahrirlash">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler-edit" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                            </a>
                                            <form action="{{ route('saas.payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Haqiqatan ham o'chirmoqchimisiz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="O'chirish">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler-trash" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center"><small>Bu kompaniya uchun hali to'lovlar kiritilmagan.</small></td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
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
