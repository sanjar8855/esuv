@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <p>
                        <strong>Shahar:</strong>
                        <a href="{{ route('cities.show', $neighborhood->city->id) }}" class="badge badge-outline text-blue">
                            {{ $neighborhood->city->name }}
                        </a>
                    </p>

                    <a href="{{ route('neighborhoods.index') }}" class="btn btn-secondary">Ortga</a>

                    <h1>{{ $neighborhood->name }} mahallasidagi ko‘chalar</h1>

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Ko‘cha nomi</th>
                            <th>Mijozlar soni</th>
                            <th>Harakatlar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($streets as $street)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('streets.show', $street->id) }}" class="badge badge-outline text-blue">
                                        {{ $street->name }}
                                    </a>
                                </td>
                                <td>{{ $street->customer_count }}</td>
                                <td>
                                    <a href="{{ route('streets.show', $street->id) }}" class="btn btn-info btn-sm">Ko‘rish</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $streets->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
