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

                    <form action="{{ route('meter_readings.update', $meterReading->id) }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="water_meter_id">Hisoblagich tanlang:</label>
                            <select name="water_meter_id" class="form-control" required>
                                @foreach($waterMeters as $waterMeter)
                                    <option
                                        value="{{ $waterMeter->id }}" {{ $meterReading->water_meter_id == $waterMeter->id ? 'selected' : '' }}>
                                        {{ $waterMeter->meter_number }} - {{ $waterMeter->customer->name ?? 'Mijoz topilmadi' }}
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
                            <label class="form-label">O‘qish sanasi:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1"><path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                            d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path
                                            d="M12 15v3"/></svg>
                                </span>
                                <input name="reading_date" type="text" class="form-control" placeholder="Sanani tanlang"
                                       required value="{{ old('reading_date', $meterReading->reading_date->format('Y-m-d')) }}"
                                       id="datepicker-icon-prepend"/>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    window.Litepicker && (new Litepicker({
                                        element: document.getElementById('datepicker-icon-prepend'),
                                        format: 'YYYY-MM-DD',
                                        buttonText: {
                                            previousMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                                            nextMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                                        },
                                    }));
                                });
                            </script>
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
    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>
@endsection
