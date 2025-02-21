@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagich O‘qishlari</h1>
                    <a href="{{ route('meter_readings.create') }}" class="btn btn-primary mb-3">Yangi O‘qish
                        Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mijoz</th>
                                    <th>Hisoblagich raqami</th>
                                    <th>O‘qish</th>
                                    <th>O‘qish sanasi</th>
                                    <th>Holat</th>
                                    <th>Harakatlar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($meterReadings as $meterReading)
                                    <tr>
                                        <td>{{ $meterReading->id }}</td>
                                        <td>
                                            <a href="{{ route('customers.show', $meterReading->waterMeter->customer->id) }}"
                                               class="badge badge-outline text-blue">
                                                {{ $meterReading->waterMeter->customer->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('water_meters.show', $meterReading->waterMeter->id) }}"
                                               class="badge badge-outline text-blue">
                                                {{ $meterReading->waterMeter->meter_number }}
                                            </a>
                                        </td>
                                        <td>{{ $meterReading->reading }}</td>
                                        <td>{{ $meterReading->reading_date }}</td>
                                        <td>{{ $meterReading->confirmed ? 'Tasdiqlangan' : 'Tasdiqlanmagan' }}</td>
                                        <td>
                                            <a href="{{ route('meter_readings.show', $meterReading->id) }}"
                                               class="btn btn-info btn-sm">Ko‘rish</a>
                                            <a href="{{ route('meter_readings.edit', $meterReading->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            <form action="{{ route('meter_readings.destroy', $meterReading->id) }}"
                                                  method="POST" style="display:inline;">
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

                    {{ $meterReadings->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
