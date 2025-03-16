@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Shaharlar</h1>
                    <a href="{{ route('cities.create') }}" class="btn btn-primary mb-3">Yangi Shahar Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-vcenter card-table table-striped">
                        <thead>
                        <tr>
                            <th> N </th>
                            <th> Viloyat </th>
                            <th> Shahar Nomi </th>
                            <th> Amallar </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($cities as $city)
                            <tr>
                                <td> {{$loop->index +1}} </td>
                                <td>{{ $city->region->name }}</td>
                                <td>{{ $city->name }}</td>
                                <td>
                                    <a href="{{ route('cities.show', $city->id) }}"
                                       class="btn btn-info btn-sm">Ko‘rish</a>
                                    <a href="{{ route('cities.edit', $city->id) }}" class="btn btn-warning btn-sm">Tahrirlash</a>
                                    @hasrole('admin')
                                    <form action="{{ route('cities.destroy', $city->id) }}" method="POST"
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

                    {{ $cities->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
