@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-md-4">
                    <h1>Hisoblagich Tafsilotlari</h1>
                    <p><strong>Mijoz:</strong> {{ $waterMeter->customer->name }}</p>
                    <p><strong>Hisoblagich Raqami:</strong> {{ $waterMeter->meter_number }}</p>
                    <p><strong>O‘rnatilgan Sana:</strong> {{ $waterMeter->installation_date ?? 'Noma’lum' }}</p>
                    <p><strong>Oxirgi tekshirilgan sana:</strong> {{ $waterMeter->last_reading_date ?? 'Noma’lum' }}</p>
                    <p><strong>Amal qilish muddati (yillarda):</strong> {{ $waterMeter->validity_period ?? 'Noma’lum' }}
                    </p>
                    <p><strong>Tugash muddati:</strong> {{ $waterMeter->expiration_date ?? 'Noma’lum' }}</p>
                    <a href="{{ route('water_meters.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
                <div class="col-md-4">
                    <h1>Hisoblagich so'ngi ko'rsatgichlari:</h1>
                    <ul class="list-group">
                        @foreach($waterMeter->readings as $reading)
                            <li class="list-group-item">
                                <small>Sana: {{ $reading->reading_date }}</small><br>
                                <small>Ko'rsatgich: {{ $reading->reading }}</small><br>
                                @if($reading->photo)
                                    <a href="{{ asset('storage/' . $reading->photo) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $reading->photo) }}" alt="Ko'rsatkich rasmi"
                                             width="50">
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4">
                    <h1>Yangi ko'rsatkich qo'shish:</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('meter_readings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="water_meter_id" value="{{ $waterMeter->id }}">

                        <div class="mb-3">
                            <label for="reading">Ko'rsatgich:</label>
                            <input type="number" name="reading" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">O‘qish sanasi:</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="icon icon-1"><path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                            d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path
                                            d="M12 15v3"/></svg>
                                </span>
                                <input name="reading_date" class="form-control" placeholder="Sanani tanlang" required
                                       value="{{ old('reading_date', now()->format('Y-m-d')) }}"
                                       id="datepicker-icon-prepend"/>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Rasm yuklash</label>
                            <input type="file" name="photo" class="form-control">
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

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector("form").addEventListener("submit", function (event) {
                event.preventDefault();

                let formData = new FormData(this);
                fetch("{{ route('meter_readings.store') }}", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Yangi ko'rsatkichni yuklash
                        } else {
                            alert("Xatolik yuz berdi!");
                        }
                    });
            });
        });
    </script>
@endsection
