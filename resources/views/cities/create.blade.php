@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Yangi Shahar Qo‘shish</h1>
                    <form action="{{ route('cities.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="region_id">Viloyat tanlang:</label>
                            <select name="region_id" class="form-control" required>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name">Shahar nomi:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
