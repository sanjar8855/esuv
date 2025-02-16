@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Mahallalar</h1>
                    <a href="{{ route('neighborhoods.create') }}" class="btn btn-primary mb-3">Yangi Mahalla
                        Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>N</th>
                            <th>Shahar</th>
                            <th>Mahalla Nomi</th>
                            <th>Harakatlar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($neighborhoods as $neighborhood)
                            <tr>
                                <td>{{$loop->index +1}}</td>
                                <td>{{ $neighborhood->city->name }}</td>
                                <td>{{ $neighborhood->name }}</td>
                                <td>
                                    <a href="{{ route('neighborhoods.show', $neighborhood->id) }}"
                                       class="btn btn-info btn-sm">Ko‘rish</a>
                                    <a href="{{ route('neighborhoods.edit', $neighborhood->id) }}"
                                       class="btn btn-warning btn-sm">Tahrirlash</a>
                                    <form action="{{ route('neighborhoods.destroy', $neighborhood->id) }}" method="POST"
                                          style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{ $neighborhoods->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
