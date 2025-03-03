@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagichni Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('water_meters.update', $waterMeter->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="customer_id">Mijoz tanlang:</label>
                            <select name="customer_id" class="form-control" required>
                                @foreach($customers as $customer)
                                    <option
                                        value="{{ $customer->id }}" {{ $waterMeter->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="meter_number">Hisoblagich Raqami:</label>
                            <input type="number" name="meter_number" class="form-control"
                                   value="{{ $waterMeter->meter_number }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="validity_period">Amal qilish muddati:</label>
                            <input type="number" name="validity_period" value="{{ $waterMeter->validity_period }}" class="form-control" required>
                        </div>

{{--                        <div class="mb-3">--}}
{{--                            <label for="installation_date">O‘rnatilgan Sana:</label>--}}
{{--                            <input type="date" name="installation_date" class="form-control"--}}
{{--                                   value="{{ $waterMeter->installation_date }}">--}}
{{--                        </div>--}}

                        <div class="mb-3">
                            <label class="form-label">O‘rnatilgan Sana:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </span>
                                <input value="{{ $waterMeter->installation_date }}" name="installation_date" class="form-control" placeholder="Sanani tanlang" id="datepicker-icon-prepend1"/>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    window.Litepicker && (new Litepicker({
                                        element: document.getElementById('datepicker-icon-prepend1'),
                                        format: 'YYYY-MM-DD',
                                        dropdowns: {
                                            minYear: 2000,  // Boshlang‘ich yil
                                            maxYear: new Date().getFullYear(),  // Hozirgi yildan keyingi 10 yilgacha
                                            months: true,  // Oynilar dropdownda chiqishi uchun
                                            years: true  // Yillarni dropdown shaklida chiqarish
                                        },
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
                            <label class="form-label">Oxirgi tekshirilgan sana:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </span>
                                <input {{ $waterMeter->last_reading_date }} name="last_reading_date" class="form-control" placeholder="Sanani tanlang" id="datepicker-icon-prepend2"/>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    window.Litepicker && (new Litepicker({
                                        element: document.getElementById('datepicker-icon-prepend2'),
                                        format: 'YYYY-MM-DD',
                                        dropdowns: {
                                            minYear: 2000,  // Boshlang‘ich yil
                                            maxYear: new Date().getFullYear(),  // Hozirgi yildan keyingi 10 yilgacha
                                            months: true,  // Oynilar dropdownda chiqishi uchun
                                            years: true  // Yillarni dropdown shaklida chiqarish
                                        },
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

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>

@endsection
