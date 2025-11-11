@extends('layouts.app')

{{-- ✅ CSS ni head ga qo'shish --}}
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
@section('content')
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
                    <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
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
                            <label class="form-label required">Ism</label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $customer->name) }}"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $customer->phone) }}"
                                   placeholder="901234567"
                                   autocomplete="off"/>
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Uy raqami</label>
                            <input type="text" name="address"
                                   class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address', $customer->address) }}">
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Hisob raqami</label>
                            <input type="text" name="account_number"
                                   class="form-control @error('account_number') is-invalid @enderror"
                                   value="{{ old('account_number', $customer->account_number) }}"
                                   required>
                            @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pdf_file" class="form-label">Shartnoma PDF</label>

                            {{-- ✅ Eski fayl ko'rsatish --}}
                            @if($customer->pdf_file)
                                <div class="mb-2">
                                    <span class="text-muted">Hozirgi fayl:</span>
                                    <a href="{{ asset('storage/' . $customer->pdf_file) }}" target="_blank" class="btn btn-sm btn-info ms-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                        PDF ni ko'rish
                                    </a>
                                </div>
                            @endif

                            <input type="file" name="pdf_file" id="pdf_file"
                                   class="form-control @error('pdf_file') is-invalid @enderror"
                                   accept=".pdf">

                            @error('pdf_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="form-hint">
                                @if($customer->pdf_file)
                                    Yangi fayl yuklasangiz, eski fayl o'chiriladi.
                                @else
                                    PDF formatdagi fayl yuklashingiz mumkin (Maksimal: 2MB)
                                @endif
                            </small>
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
                            <input type="number" name="family_members" id="family_members"
                                   class="form-control @error('family_members') is-invalid @enderror"
                                   value="{{ old('family_members', $customer->family_members) }}"
                                   min="1" max="50">
                            @error('family_members')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            new TomSelect("#StreetSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Ko'chani tanlang...",
                allowEmptyOption: true
            });
        });
    </script>
@endpush
