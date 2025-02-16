@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Shaharni Tahrirlash</h1>
                    <form action="{{ route('cities.update', $city->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="region_id">Viloyat tanlang:</label>
                            <select name="region_id" class="form-control" required>
                                @foreach($regions as $region)
                                    <option
                                        value="{{ $region->id }}" {{ $city->region_id == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name">Shahar nomi:</label>
                            <input type="text" name="name" class="form-control" value="{{ $city->name }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
