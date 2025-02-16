@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Ko‘chani Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('streets.update', $street->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="neighborhood_id">Mahalla tanlang:</label>
                            <select name="neighborhood_id" class="form-control" required>
                                @foreach($neighborhoods as $neighborhood)
                                    <option
                                        value="{{ $neighborhood->id }}" {{ $street->neighborhood_id == $neighborhood->id ? 'selected' : '' }}>
                                        {{ $neighborhood->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name">Ko‘cha nomi:</label>
                            <input type="text" name="name" class="form-control" value="{{ $street->name }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
