@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Yangi Ko‘cha Qo‘shish</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('streets.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label required" for="company_id">Kompaniya</label>
                            <select name="company_id" id="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">Kompaniyani tanlang...</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="neighborhood_id">Mahalla kiritib tanlang:</label>
                            <select name="neighborhood_id" id="neighborhoodSelect" class="form-control" required>
                                <option></option>
                                @foreach($neighborhoods as $neighborhood)
                                    <option value="{{ $neighborhood->id }}">
                                        @if($neighborhood->company) (Komp: {{ $neighborhood->company->name }}), @endif
                                        {{ $neighborhood->name }},
                                        {{$neighborhood->city->name}},
                                        {{$neighborhood->city->region->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name">Ko‘cha nomi:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#neighborhoodSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Mahalla nomini yozing...",
                allowEmptyOption: true
            });

            new TomSelect("#company_id", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Kompaniya nomini yozing...",
                allowEmptyOption: true
            });
        });
    </script>
@endsection
