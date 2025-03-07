@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagich O‘qishini Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('meter_readings.update', $meterReading->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="water_meter_id">Hisoblagich tanlang:</label>
                            <select name="water_meter_id" class="form-control" required>
                                @foreach($waterMeters as $waterMeter)
                                    <option
                                        value="{{ $waterMeter->id }}" {{ $meterReading->water_meter_id == $waterMeter->id ? 'selected' : '' }}>
                                        {{ $waterMeter->meter_number }} - {{ $waterMeter->customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reading">O‘qish:</label>
                            <input type="number" name="reading" class="form-control"
                                   value="{{ $meterReading->reading }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="reading_date">O‘qish sanasi:</label>
                            <input type="date" name="reading_date" class="form-control"
                                   value="{{ $meterReading->reading_date }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="photo_url">O‘qish rasmi URL:</label>
                            <input type="text" name="photo_url" class="form-control"
                                   value="{{ $meterReading->photo_url }}">
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Rasm yuklash</label>
                            <input type="file" name="photo" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="confirmed">Tasdiqlanganmi?</label>
                            <select name="confirmed" class="form-control">
                                <option value="1" {{ $meterReading->confirmed ? 'selected' : '' }}>Ha</option>
                                <option value="0" {{ !$meterReading->confirmed ? 'selected' : '' }}>Yo‘q</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
