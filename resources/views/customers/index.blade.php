@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Mijozlar, {{$customersCount}} ta</h2>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary mb-3">Yangi mijoz qo‘shish</a>
                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Mijoz ismi, telefoni yoki hisob raqami" class="form-control">
                            <button type="submit" class="btn btn-primary">Qidirish</button>
                        </div>
                    </form>

                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <select name="street_id" id="StreetSelect" class="form-control">
                                <option value="">Barcha ko‘chalar</option>
                                @foreach($streets as $street)
                                    <option
                                        value="{{ $street->id }}" {{ request('street_id') == $street->id ? 'selected' : '' }}>
                                        {{ $street->name }} ko'cha, {{ $street->neighborhood->name }} mahalla, {{ $street->neighborhood->city->name }}, {{ $street->neighborhood->city->region->name }} viloyat
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Filtrlash</button>
                        </div>
                    </form>

                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <select name="debt" class="form-control">
                                <option value="">Barcha mijozlar</option>
                                <option value="has_debt" {{ request('debt') == 'has_debt' ? 'selected' : '' }}>Faqat
                                    qarzdorlar
                                </option>
                            </select>
                            <button type="submit" class="btn btn-primary">Filtrlash</button>
                        </div>
                    </form>

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    @if(auth()->user()->hasRole('admin'))
                                        <th>Kompaniya</th>
                                    @endif
                                    <th>Ko‘cha</th>
                                    <th>Uy raqami</th>
                                    <th>Ism</th>
                                    <th>Telefon</th>
                                    <th>Jami Qarzdorlik (UZS)</th>
                                    <th>Oxirgi ko'rsatkich</th>
                                    <th>Oila a'zolari soni</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        @if(auth()->user()->hasRole('admin'))
                                            <td>
                                                <a href="{{ route('companies.show', $customer->company->id) }}">
                                                    {{ $customer->company->name }}
                                                </a>
                                            </td>
                                        @endif
                                        <td>
                                            <a href="{{ route('streets.show', $customer->street->id) }}">
                                                {{ $customer->street->name }}
                                            </a>
                                        </td>
                                        <td>{{ $customer->address }}</td>
                                        <td>
                                            {{ $customer->name }}
                                            @if($customer->is_active)
                                                <span class="badge bg-cyan text-cyan-fg">Faol</span>
                                            @else
                                                <span class="badge bg-red text-red-fg">Nofaol</span>
                                            @endif
                                        </td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>
                                            @php
                                                $balance = $customer->balance;
                                                $balanceClass = $balance < 0 ? 'text-red' : ($balance > 0 ? 'text-green' : 'text-info');
                                                $balanceText = $balance < 0 ? 'Qarzdor' : ($balance > 0 ? 'Ortiqcha' : 'Nol');
                                            @endphp
                                            <span class="badge {{ $balanceClass }}">
                                        {{ ($balance > 0 ? '+' : '-') . number_format(abs($balance)) }} UZS ({{ $balanceText }})
                                    </span>
                                        </td>
                                        <td>
                                            @if($customer->waterMeter && $customer->waterMeter->readings->count())
                                                {{ $customer->waterMeter->readings->first()->reading }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $customer->family_members }}</td>
                                        <td>
                                            <a href="{{ route('customers.show', $customer->id) }}"
                                               class="btn btn-info btn-sm">Batafsil</a>
                                            <a href="{{ route('customers.edit', $customer->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Haqiqatan ham o‘chirmoqchimisiz?')">
                                                    O‘chirish
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sahifalash (pagination) -->
                    <div class="mt-3">
                        {{ $customers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#StreetSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Mahalla nomini yozing...",
                allowEmptyOption: true
            });
        });
    </script>
@endsection
