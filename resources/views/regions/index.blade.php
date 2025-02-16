@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Viloyatlar</h1>
                    <a href="{{ route('regions.create') }}" class="btn btn-primary mb-3">Yangi viloyat qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>N</th>
                            <th>Nomi</th>
                            <th>Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($regions as $region)
                            <tr>
                                <td>{{$loop->index +1}}</td>
                                <td>{{ $region->name }}</td>
                                <td>
                                    <a href="{{ route('regions.show', $region->id) }}" class="btn btn-info btn-sm">Batafsil</a>
                                    <a href="{{ route('regions.edit', $region->id) }}" class="btn btn-warning btn-sm">Tahrirlash</a>
                                    <form action="{{ route('regions.destroy', $region->id) }}" method="POST"
                                          style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{ $regions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
