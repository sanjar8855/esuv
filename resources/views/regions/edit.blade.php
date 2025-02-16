@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hududni Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('regions.update', $region->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name">Hudud nomi:</label>
                            <input type="text" name="name" class="form-control" value="{{ $region->name }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
