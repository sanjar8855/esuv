@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Yangi Hisoblagich O‘qish Qo‘shish</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('meter_readings.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="water_meter_id">Hisoblagich tanlang:</label>
                            <select name="water_meter_id" class="form-control" required>
                                @foreach($waterMeters as $waterMeter)
                                    <option value="{{ $waterMeter->id }}">{{ $waterMeter->meter_number }}
                                        - {{ $waterMeter->customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reading">O‘qish:</label>
                            <input type="number" name="reading" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="reading_date">O‘qish sanasi:</label>
                            <input type="date" name="reading_date" class="form-control" required value="{{ old('reading_date', now()->format('Y-m-d')) }}">
                        </div>

                        <div class="mb-3">
                            <label for="confirmed">Tasdiqlanganmi?</label>
                            <select name="confirmed" class="form-control">
                                <option value="1">Ha</option>
                                <option value="0">Yo‘q</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
