@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>{{ $city->name }} shahardagi mahallalar</h1>
                    <a href="{{ route('cities.index') }}" class="btn btn-secondary">Ortga</a>

                    <table class="table table-bordered mt-3">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Mahalla nomi</th>
                            <th>Ko‘chalar soni</th>
                            <th>Mijozlar soni</th>
                            <th>Harakatlar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($neighborhoods as $neighborhood)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('neighborhoods.show', $neighborhood->id) }}" class="badge badge-outline text-blue">
                                        {{ $neighborhood->name }}
                                    </a>
                                </td>
                                <td>{{ $neighborhood->street_count }}</td>
                                <td>{{ $neighborhood->customer_count }}</td>
                                <td>
                                    <a href="{{ route('neighborhoods.show', $neighborhood->id) }}" class="btn btn-info btn-sm">Ko‘rish</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $neighborhoods->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
