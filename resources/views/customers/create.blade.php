@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Yangi mijoz qo‘shish</h2>
                    <h4 class="d-flex">
                        <label class="form-label required"></label>
                        &nbsp; Majburiy to'ldirilishi kerak bo'lgan ma'lumotlar
                    </h4>
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
                            <label class="form-label required">Kompaniya</label>
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
                            <label class="form-label required">Ko'cha</label>
                            <select name="street_id" id="StreetSelect" class="form-control" required>
                                <option></option>
                                @foreach($streets as $street)
                                    <option value="{{ $street->id }}">
                                        @if(auth()->user()->hasRole('admin') && $street->company)
                                            <span class="text-primary">[{{ $street->company->name }}]</span>
                                        @endif
                                        {{ $street->name }} ko'cha,
                                        {{ $street->neighborhood->name }} mahalla,
                                        {{ $street->neighborhood->city->name }},
                                        {{ $street->neighborhood->city->region->name }} viloyat
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">FIO</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name')}}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Hisob raqami (Hisoblagich raqami)</label>
                            <input type="text" name="account_meter_number"
                                   class="form-control @error('account_meter_number') is-invalid @enderror"
                                   value="{{ old('account_meter_number') }}" required>
                            @error('account_meter_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-check form-switch form-switch-2">
                                <input class="form-check-input" type="checkbox" id="has_water_meter_checkbox"
                                       name="has_water_meter"
                                       value="1" {{ old('has_water_meter', true) ? 'checked' : '' }}>
                                <span class="form-check-label">Suv hisoblagichi bormi? Meyoriy bo'lsa o'chirib qo'ying</span>
                            </label>
                        </div>

                        <div id="meter_number_div">
                            <div class="mb-3">
                                <label class="form-label required">Boshlang‘ich Ko‘rsatkich</label>
                                <input type="number" name="initial_reading" class="form-control">
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
                                    <input name="reading_date" class="form-control" placeholder="Sanani tanlang"
                                           required value="{{ old('reading_date', now()->format('Y-m-d')) }}"
                                           id="datepicker-icon-prepend"/>
                                </div>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        window.Litepicker && (new Litepicker({
                                            element: document.getElementById('datepicker-icon-prepend'),
                                            format: 'YYYY-MM-DD',
                                            buttonText: {
                                                previousMonth: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                                                nextMonth: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                                            },
                                        }));
                                    });
                                </script>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone')}}"
                                   data-mask="(00) 000-00-00" data-mask-visible="true" placeholder="(00) 000-00-00"
                                   autocomplete="off"/>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Uy raqami</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Oila a'zolari soni, agar meyoriy bo'lsa kiritish majburiy</label>
                            <input type="number" name="family_members" id="family_members" class="form-control"
                                   value="{{ old('family_members') }}" min="1">
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
            const hasWaterMeterCheckbox = document.getElementById("has_water_meter_checkbox");
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
