@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagichlar, {{$waterMetersCount}} ta</h1>
                    <a href="{{ route('water_meters.create') }}" class="btn btn-primary mb-3">Yangi Hisoblagich
                        Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Mijoz</th>
                                    <th>Hisoblagich unikal raqami</th>
                                    <th>O‘rnatilgan Sana</th>
                                    <th>Amal qilish muddati yillarda</th>
                                    <th>Tugash sanasi</th>
                                    <th>Oxirgi O‘qish</th>
                                    <th>Oxirgi ko‘rsatkich</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($waterMeters as $waterMeter)
                                    <tr>
                                        <td>{{$loop->index +1}}</td>
                                        <td>
                                            @if ($waterMeter->customer)
                                                <a href="{{ route('customers.show', $waterMeter->customer->id) }}"
                                                   class="badge badge-outline text-blue">
                                                    {{ $waterMeter->customer->name }}
                                                </a>
                                            @else
                                                <span class="badge badge-outline text-danger">Mijoz yo‘q</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($waterMeter->meter_number, 0, '.', ' ') }}</td>
                                        <td>{{ $waterMeter->last_reading_date ?? 'Noma’lum' }}</td>
                                        <td>{{ $waterMeter->validity_period ?? 'Noma’lum' }}</td>
                                        <td>{{ $waterMeter->expiration_date ?? 'Noma’lum' }}</td>
                                        <td>{{ $waterMeter->installation_date ?? 'Noma’lum' }}</td>
                                        <td>
                                            @if($waterMeter->readings->count())
                                                {{ number_format($waterMeter->readings->first()->reading, 0, '.', ' ') }}
                                            @else
                                                ---
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('water_meters.show', $waterMeter->id) }}"
                                               class="btn btn-info btn-sm">Ko‘rish</a>
                                            <a href="{{ route('water_meters.edit', $waterMeter->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            <form action="{{ route('water_meters.destroy', $waterMeter->id) }}"
                                                  method="POST"
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

                    {{ $waterMeters->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
