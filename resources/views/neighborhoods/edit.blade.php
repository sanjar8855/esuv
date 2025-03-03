@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Mahallani Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('neighborhoods.update', $neighborhood->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="city_id">Shahar tanlang:</label>
                            <select name="city_id" class="form-control" required>
                                @foreach($cities as $city)
                                    <option
                                        value="{{ $city->id }}" {{ $neighborhood->city_id == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }} - {{$city->region->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name">Mahalla nomi:</label>
                            <input type="text" name="name" class="form-control" value="{{ $neighborhood->name }}"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
