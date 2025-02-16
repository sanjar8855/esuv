@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagichlar</h1>
                    <a href="{{ route('water_meters.create') }}" class="btn btn-primary mb-3">Yangi Hisoblagich
                        Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>N</th>
                            <th>Mijoz</th>
                            <th>Hisoblagich unikal raqami</th>
                            <th>Oxirgi ko‘rsatkich</th>
                            <th>O‘rnatilgan Sana</th>
                            <th>Oxirgi O‘qish</th>
                            <th>Harakatlar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($waterMeters as $waterMeter)
                            <tr>
                                <td>{{$loop->index +1}}</td>
                                <td>
                                    @if ($waterMeter->customer)
                                        <a href="{{ route('customers.show', $waterMeter->customer->id) }}" class="badge badge-outline text-blue">
                                            {{ $waterMeter->customer->name }}
                                        </a>
                                    @else
                                        <span class="badge badge-outline text-danger">Mijoz yo‘q</span>
                                    @endif
                                </td>
                                <td>{{ $waterMeter->meter_number }}</td>
                                <td>
                                    @if($waterMeter->readings->count())
                                        {{ $waterMeter->readings->first()->reading }}
                                    @else
                                        ---
                                    @endif
                                </td>
                                <td>{{ $waterMeter->installation_date ?? 'Noma’lum' }}</td>
                                <td>{{ $waterMeter->last_reading_date ?? 'Noma’lum' }}</td>
                                <td>
                                    <a href="{{ route('water_meters.show', $waterMeter->id) }}"
                                       class="btn btn-info btn-sm">Ko‘rish</a>
                                    <a href="{{ route('water_meters.edit', $waterMeter->id) }}"
                                       class="btn btn-warning btn-sm">Tahrirlash</a>
                                    <form action="{{ route('water_meters.destroy', $waterMeter->id) }}" method="POST"
                                          style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{ $waterMeters->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
