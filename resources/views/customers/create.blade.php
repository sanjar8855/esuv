@extends('layouts.app')

@section('content')
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
                    <form action="{{ route('customers.store') }}" method="POST">
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
                                <input type="text" class="form-control" value="{{ $companies->first()->name }}" disabled>
                                <input type="hidden" name="company_id" value="{{ $companies->first()->id }}">
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ismi</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name')}}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ko'cha</label>
                            <select name="street_id" class="form-control" required>
                                @foreach($streets as $street)
                                    <option value="{{ $street->id }}">{{ $street->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
{{--                            <input type="text" name="phone" class="form-control" value="{{ old('phone')}}">--}}
                            <input type="text" name="phone" class="form-control"  value="{{ old('phone')}}" data-mask="(00) 000-00-00" data-mask-visible="true" placeholder="(00) 000-00-00" autocomplete="off"/>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Manzil</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hisob raqami</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-check form-switch form-switch-2">
                                <input class="form-check-input" type="checkbox" id="has_water_meter" name="has_water_meter" value="1" {{ old('has_water_meter') ? 'checked' : '' }}>
                                <span class="form-check-label">Suv hisoblagichi bormi?</span>
                            </label>

{{--                            <label class="form-check">--}}
{{--                                <input type="checkbox" name="has_water_meter" id="has_water_meter" class="form-check-input"--}}
{{--                                       value="1" {{ old('has_water_meter') ? 'checked' : '' }}>--}}
{{--                                <span class="form-check-label">Suv hisoblagichi bormi?</span>--}}
{{--                            </label>--}}
                        </div>

                        <div class="mb-3" id="family_members_div">
                            <label class="form-label">Oila a'zolari soni</label>
                            <input type="number" name="family_members" id="family_members" class="form-control" min="1"
                                   value="{{ old('family_members') }}">
                        </div>

                        <div class="mb-3">
{{--                            <div class="form-label">Single switch</div>--}}
                            <label class="form-check form-switch form-switch-2">
                                <input class="form-check-input" type="checkbox" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <span class="form-check-label">Faol mijozmi?</span>
                            </label>

{{--                            <label class="form-check">--}}
{{--                                <input type="checkbox" name="is_active" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>--}}
{{--                                <span class="form-check-label">Faol mijoz</span>--}}
{{--                            </label>--}}
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Bekor qilish</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const hasWaterMeterCheckbox = document.getElementById("has_water_meter");
            const familyMembersDiv = document.getElementById("family_members_div");
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
                    familyMembersDiv.style.display = "none";
                    familyMembersInput.value = ""; // Oiladagi odam soni olinmaydi
                } else {
                    // Hisoblagich yo‘q bo‘lsa
                    if (meterNumberDiv) {
                        meterNumberDiv.style.display = "none";
                        meterNumberInput.value = "";
                    }
                    familyMembersDiv.style.display = "block";
                }
            }

            hasWaterMeterCheckbox.addEventListener("change", toggleFields);
            toggleFields(); // Sahifa yuklanganda avtomatik tekshirish
        });
    </script>
@endsection
