@extends('layouts.app')

@section('content')

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>
                        {{ $region->name }} viloyati
                        @if($region->is_active)
                            <span class="badge bg-success">Faol</span>
                        @else
                            <span class="badge bg-danger">Faol emas</span>
                        @endif
                    </h2>

                    <table class="table">
                        <thead>
                        <tr>
                            <th>Nomi:</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($region->cities as $city)
                            <tr>
                                <td>{{ $city->name }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <a href="{{ route('regions.index') }}" class="btn btn-secondary">Orqaga</a>
                    <a href="{{ route('regions.edit', $region->id) }}" class="btn btn-warning">Tahrirlash</a>

                    @hasrole('admin')
                    <form action="{{ route('regions.destroy', $region->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Haqiqatan ham o‘chirilsinmi?')">O‘chirish
                        </button>
                    </form>
                    @endhasrole
                </div>
            </div>
        </div>
    </div>

@endsection
