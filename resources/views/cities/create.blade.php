@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <form action="{{ route('cities.store') }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Yangi Shahar/Tuman Qo‘shish</h4>
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
                                    <label class="form-label" for="company_id">Kompaniya (Ixtiyoriy):</label>
                                    <select name="company_id" id="company_id"
                                            class="form-select @error('company_id') is-invalid @enderror">
                                        <option value="">-- Kompaniya tanlanmagan --
                                        </option> {{-- NULL qiymat uchun --}}
                                        @foreach($companies as $company)
                                            <option
                                                value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required" for="region_id">Viloyat tanlang:</label>
                                    <select name="region_id" id="region_id"
                                            class="form-select @error('region_id') is-invalid @enderror" required>
                                        <option value="">Viloyatni tanlang...</option>
                                        @foreach($regions as $region)
                                            <option
                                                value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('region_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required" for="name">Shahar/Tuman nomi:</label>
                                    <input type="text" name="name" id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <a href="{{ route('cities.index') }}" class="btn">Bekor qilish</a>
                                <button type="submit" class="btn btn-primary">Saqlash</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- TomSelect yoki boshqa select kutubxonasi uchun skriptlar --}}
    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css"
              rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                if (document.getElementById('region_id')) {
                    new TomSelect('#region_id', {
                        create: false,
                        sortField: {field: "text", direction: "asc"},
                        placeholder: "Viloyatni tanlang..."
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
