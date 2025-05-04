@extends('layouts.app')

@section('content')

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>
                        {{ $region->name }} viloyati
                    </h2>

                    <table class="table">
                        <thead>
                        <tr>
                            <th>Nomi:</th>
                            <th>Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($region->cities as $city)
                            <tr>
                                <td>{{ $city->name }}</td>
                                <td>
                                    <a href="{{ route('cities.show', $city->id) }}"
                                       class="btn btn-sm btn-info">Batafsil</a>
                                    @hasrole('admin')
                                    <a href="{{ route('cities.edit', $city->id) }}"
                                       class="btn btn-sm btn-warning">Tahrirlash</a>
                                    <form action="{{ route('cities.destroy', $city->id) }}" method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Haqiqatan ham o‘chirilsinmi?')">O‘chirish
                                        </button>
                                    </form>
                                    @endhasrole
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <a href="{{ route('regions.index') }}" class="btn btn-secondary">Orqaga</a>

                    @hasrole('admin')
                    <a href="{{ route('regions.edit', $region->id) }}" class="btn btn-warning">Tahrirlash</a>
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
