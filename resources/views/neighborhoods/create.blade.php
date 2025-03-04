@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Yangi Mahalla Qo‘shish</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('neighborhoods.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="city_id">Shahar/tuman ni tanlang:</label>
                            <select name="city_id" id="citySelect" class="form-control" required>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }} - {{$city->region->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name">Mahalla nomi:</label>
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
            new TomSelect("#citySelect", {
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
