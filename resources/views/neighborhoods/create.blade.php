@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <form action="{{ route('neighborhoods.store') }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Yangi Mahalla Qo‘shish</h4>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <strong>Xatoliklar mavjud:</strong>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label" for="company_id">Kompaniya (Ixtiyoriy - Agar shaharda
                                        kompaniya ko'rsatilmagan bo'lsa yoki boshqa kompaniyaga biriktirmoqchi
                                        bo'lsangiz):</label>
                                    <select name="company_id" id="company_id"
                                            class="form-select @error('company_id') is-invalid @enderror">
                                        <option value="">-- Kompaniya tanlanmagan --</option>
                                        @foreach($companies as $company)
                                            <option
                                                value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-hint">Agar bu yerda kompaniya tanlanmasa va tanlangan shaharning
                                        o'z kompaniyasi bo'lsa, mahalla o'sha shahar kompaniyasiga biriktiriladi. Agar
                                        shahar ham kompaniyasiz bo'lsa, mahalla ham kompaniyasiz bo'ladi.</small>
                                    @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required" for="city_id">Shahar/Tuman tanlang:</label>
                                    <select name="city_id" id="city_id"
                                            class="form-select @error('city_id') is-invalid @enderror" required>
                                        <option value="">Shaharni tanlang...</option>
                                        @foreach($cities as $city)
                                            <option
                                                value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                                {{ $city->name }}
                                                @if($city->region) ({{ $city->region->name }}) @endif
                                                @if($city->company) <span
                                                    class="text-muted"> - Komp: {{ $city->company->name }}</span> @else
                                                    <span class="text-muted"> - Komp: Yo'q</span> @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required" for="name">Mahalla nomi:</label>
                                    <input type="text" name="name" id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <a href="{{ route('neighborhoods.index') }}" class="btn">Bekor qilish</a>
                                <button type="submit" class="btn btn-primary">Saqlash</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css"
              rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                if (document.getElementById('city_id')) {
                    new TomSelect('#city_id', {
                        create: false,
                        sortField: {field: "text", direction: "asc"},
                        placeholder: "Shaharni tanlang..."
                    });
                }
                if (document.getElementById('company_id')) {
                    new TomSelect('#company_id', {
                        create: false,
                        sortField: {field: "text", direction: "asc"},
                        placeholder: "Kompaniyani tanlang..."
                    });
                }
            });
        </script>
    @endpush
@endsection
