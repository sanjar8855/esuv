@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Ko‘cha Tafsilotlari</h1>
                    <p><strong>Mahalla:</strong>
                        <a href="{{ route('neighborhoods.show', $street->neighborhood->id) }}" class="badge badge-outline text-blue">
                            {{ $street->neighborhood->name }}
                        </a>
                    </p>
                    <p><strong>Ko‘cha Nomi:</strong> {{ $street->name }}</p>

                    <a href="{{ route('streets.index') }}" class="btn btn-secondary">Ortga</a>

                    <h2>{{ $street->name }} ko‘chasidagi mijozlar ({{ $customersCount }} ta)</h2>

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>N</th>
                            @if(auth()->user()->hasRole('admin'))
                                <th>Kompaniya</th>
                            @endif
                            <th>Uy raqami</th>
                            <th>Ism</th>
                            <th>Telefon</th>
                            <th>Hisoblagich</th>
                            <th>Qarzdorlik</th>
                            <th>Oxirgi ko'rsatkich</th>
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
                                <td>{{ $customer->address }}</td>
                                <td>
                                    <a href="{{ route('customers.show', $customer->id) }}"
                                       class="badge badge-outline text-blue">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td>
                                    @if($customer->waterMeter)
                                        <a href="{{ route('water_meters.show', $customer->waterMeter->id) }}"
                                           class="badge badge-outline text-blue">
                                            {{ $customer->waterMeter->meter_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">Hisoblagich yo‘q</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $balance = $customer->balance;
                                        $color = $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : 'text-muted');
                                    @endphp
                                    <span class="{{ $color }}">{{ number_format($balance, 0, '', ' ') }} UZS</span>
                                </td>
                                <td>
                                    @if($customer->waterMeter && $customer->waterMeter->readings->count())
                                        {{ $customer->waterMeter->readings->first()->reading }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $customers->links() }}
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
