@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Yangi mijoz qo‘shish</h2>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Kompaniya</label>
                            @if(auth()->user()->hasRole('admin'))
                                <select name="company_id" class="form-control">
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="{{ $companies->first()->name }}"
                                       disabled>
                                <input type="hidden" name="company_id" value="{{ $companies->first()->id }}">
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ismi</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name')}}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ko'cha</label>
                            <select name="street_id" id="StreetSelect" class="form-control" required>
                                <option></option>
                                @foreach($streets as $street)
                                    <option value="{{ $street->id }}">{{ $street->name }} ko'cha, {{ $street->neighborhood->name }} mahalla, {{ $street->neighborhood->city->name }}, {{ $street->neighborhood->city->region->name }} viloyat</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            {{--                            <input type="text" name="phone" class="form-control" value="{{ old('phone')}}">--}}
                            <input type="text" name="phone" class="form-control" value="{{ old('phone')}}"
                                   data-mask="(00) 000-00-00" data-mask-visible="true" placeholder="(00) 000-00-00"
                                   autocomplete="off"/>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Uy raqami</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hisob raqami</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="pdf_file" class="form-label">Shartnoma PDF</label>
                            <input type="file" name="pdf_file" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Oila a'zolari soni</label>
                            <input type="number" name="family_members" id="family_members" class="form-control"
                                   value="{{ old('family_members') }}" min="1">
                        </div>

                        {{--                        <div class="mb-3">--}}
                        {{--                            <label class="form-check form-switch form-switch-2">--}}
                        {{--                                <input class="form-check-input" type="checkbox" id="has_water_meter"--}}
                        {{--                                       name="has_water_meter" value="1" {{ old('has_water_meter') ? 'checked' : '' }}>--}}
                        {{--                                <span class="form-check-label">Suv hisoblagichi bormi?</span>--}}
                        {{--                            </label>--}}

                        {{--                            <label class="form-check">--}}
                        {{--                                <input type="checkbox" name="has_water_meter" id="has_water_meter" class="form-check-input"--}}
                        {{--                                       value="1" {{ old('has_water_meter') ? 'checked' : '' }}>--}}
                        {{--                                <span class="form-check-label">Suv hisoblagichi bormi?</span>--}}
                        {{--                            </label>--}}
                        {{--                        </div>--}}


                        {{--                        <div class="mb-3">--}}
                        {{--                            <div class="form-label">Single switch</div>--}}
                        {{--                            <label class="form-check form-switch form-switch-2">--}}
                        {{--                                <input class="form-check-input" type="checkbox" name="is_active" checked {{ old('is_active', true) ? 'checked' : '' }}>--}}
                        {{--                                <span class="form-check-label">Faol mijozmi?</span>--}}
                        {{--                            </label>--}}

                        {{--                            <label class="form-check">--}}
                        {{--                                <input type="checkbox" name="is_active" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>--}}
                        {{--                                <span class="form-check-label">Faol mijoz</span>--}}
                        {{--                            </label>--}}
                        {{--                        </div>--}}

                        <h3>Hisoblagich ma’lumotlari</h3>
                        <div class="mb-3">
                            <label class="form-label">Hisoblagich Raqami</label>
                            <input type="number" name="meter_number" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">O‘rnatilgan Sana:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1"><path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                            d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path
                                            d="M12 15v3"/></svg>
                                </span>
                                <input name="installation_date" class="form-control" placeholder="Sanani tanlang"
                                       id="datepicker-icon-prepend1"/>
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
                            <label class="form-label">Hisoblagich Amal Qilish Muddati (yil)</label>
                            <input type="number" name="validity_period" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Boshlang‘ich Ko‘rsatkich</label>
                            <input type="number" name="initial_reading" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">O‘qish sanasi:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1"><path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                            d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path
                                            d="M12 15v3"/></svg>
                                </span>
                                <input name="reading_date" class="form-control" placeholder="Sanani tanlang" required
                                       value="{{ old('reading_date', now()->format('Y-m-d')) }}"
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

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Bekor qilish</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const hasWaterMeterCheckbox = document.getElementById("has_water_meter");
            const familyMembersInput = document.getElementById("family_members");
            const meterNumberDiv = document.getElementById("meter_number_div");
            const meterNumberInput = document.getElementById("meter_number");

            function toggleFields() {
                if (hasWaterMeterCheckbox.checked) {
                    // Hisoblagich bo‘lsa
                    if (meterNumberDiv) {
                        meterNumberDiv.style.display = "block";
                        meterNumberInput.value = "AUTOGENERATED"; // Hisoblagich raqamini avtomatik yozish
                    }
                    familyMembersInput.value = ""; // Oiladagi odam soni olinmaydi
                } else {
                    // Hisoblagich yo‘q bo‘lsa
                    if (meterNumberDiv) {
                        meterNumberDiv.style.display = "none";
                        meterNumberInput.value = "";
                    }
                }
            }

            hasWaterMeterCheckbox.addEventListener("change", toggleFields);
            toggleFields(); // Sahifa yuklanganda avtomatik tekshirish
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
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
