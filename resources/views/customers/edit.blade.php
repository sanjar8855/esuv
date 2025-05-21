@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Mijozni tahrirlash</h2>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Kompaniya</label>
                            @if(auth()->user()->hasRole('admin'))
                                <select name="company_id" class="form-control">
                                    @foreach($companies as $company)
                                        <option
                                            value="{{ $company->id }}" {{$customer->company_id==$company->id ? 'selected' : ''}}>{{ $company->name }}</option>
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
                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ko'cha</label>
                            <select name="street_id" id="StreetSelect" class="form-control" required>
                                @foreach($streets as $street)
                                    <option
                                        value="{{ $street->id }}" {{ $customer->street_id == $street->id ? 'selected' : '' }}>
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
                            <label class="form-label">Telefon</label>
                            <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}"
                                   data-mask="(00) 000-00-00" data-mask-visible="true" placeholder="(00) 000-00-00"
                                   autocomplete="off"/>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Uy raqami</label>
                            <input type="text" name="address" class="form-control" value="{{ $customer->address }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hisob raqami</label>
                            <input type="text" name="account_number" class="form-control"
                                   value="{{ $customer->account_number }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="pdf_file" class="form-label">Shartnoma PDF</label>
                            <input type="file" name="pdf_file" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="has_water_meter" id="has_water_meter"
                                       class="form-check-input"
                                       value="1" {{ $customer->has_water_meter ? 'checked' : '' }}>
                                <span class="form-check-label">Suv hisoblagichi bormi?</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Oila a'zolari soni</label>
                            <input type="number" name="family_members" id="family_members" class="form-control"
                                   value="{{ $customer->family_members }}" min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active"
                                       class="form-check-input" {{ $customer->is_active ? 'checked' : '' }}>
                                <span class="form-check-label">Faol mijoz</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Bekor qilish</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{--    <script>--}}
    {{--        document.addEventListener("DOMContentLoaded", function () {--}}
    {{--            const hasWaterMeterCheckbox = document.getElementById("has_water_meter");--}}
    {{--            const familyMembersDiv = document.getElementById("family_members_div");--}}
    {{--            const familyMembersInput = document.getElementById("family_members");--}}

    {{--            function toggleFields() {--}}
    {{--                if (hasWaterMeterCheckbox.checked) {--}}
    {{--                    familyMembersDiv.style.display = "none";--}}
    {{--                    familyMembersInput.value = ""; // Oiladagi odam soni olinmaydi--}}
    {{--                } else {--}}
    {{--                    familyMembersDiv.style.display = "block";--}}
    {{--                }--}}
    {{--            }--}}

    {{--            hasWaterMeterCheckbox.addEventListener("change", toggleFields);--}}
    {{--            toggleFields(); // Sahifa yuklanganda avtomatik tekshirish--}}
    {{--        });--}}
    {{--    </script>--}}
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
