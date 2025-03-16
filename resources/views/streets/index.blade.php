@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Ko‘chalar</h1>
                    <a href="{{ route('streets.create') }}" class="btn btn-primary mb-3">Yangi Ko‘cha Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Mahalla</th>
                                    <th>Ko‘cha Nomi</th>
                                    <th>Harakatlar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($streets as $street)
                                    <tr>
                                        <td>{{$loop->index +1}}</td>
                                        <td>{{ $street->neighborhood->name }}</td>
                                        <td>{{ $street->name }}</td>
                                        <td>
                                            <a href="{{ route('streets.show', $street->id) }}"
                                               class="btn btn-info btn-sm">Ko‘rish</a>
                                            <a href="{{ route('streets.edit', $street->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            @hasrole('admin')
                                            <form action="{{ route('streets.destroy', $street->id) }}" method="POST"
                                                  style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                            </form>
                                            @endhasrole
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{ $streets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
