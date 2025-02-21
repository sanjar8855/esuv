@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Tariflar Ro‘yxati</h2>
                    <a href="{{ route('tariffs.create') }}" class="btn btn-primary mb-3">Yangi Tarif qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    {{--                            <th>Tarif nomi</th>--}}
                                    @if(auth()->user()->hasRole('admin'))
                                        <th>Kompaniya</th>
                                    @endif
                                    <th>1m³ narxi (UZS)</th>
                                    <th>Bir kishiga (UZS)</th>
                                    <th>Boshlanish sanasi</th>
                                    <th>Tugash sanasi</th>
                                    {{--                            <th>Holat</th>--}}
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tariffs as $tariff)
                                    <tr>
                                        <td>{{$loop->index +1}}</td>
                                        @if(auth()->user()->hasRole('admin'))
                                            <td>
                                                <a href="{{ route('companies.show', $tariff->company->id) }}">{{$tariff->company->name}}</a>
                                            </td> @endif
                                        {{--                                <td>{{ $tariff->name }}</td>--}}
                                        <td>{{ $tariff->price_per_m3 }}</td>
                                        <td>{{ $tariff->for_one_person }}</td>
                                        <td>{{ $tariff->valid_from }}</td>
                                        <td>{{ $tariff->valid_to ?? 'Cheklanmagan' }}</td>
                                        {{--                                <td>--}}
                                        {{--                                    <span class="badge bg-{{ $tariff->is_active ? 'azure' : 'red' }} text-{{ $tariff->is_active ? 'azure' : 'red' }}-fg">--}}
                                        {{--                                        {{ $tariff->is_active ? 'Aktiv' : 'Nofaol' }}--}}
                                        {{--                                    </span>--}}
                                        {{--                                </td>--}}
                                        <td>
                                            <a href="{{ route('tariffs.show', $tariff->id) }}" class="btn btn-info">Ko'rish</a>
                                            <a href="{{ route('tariffs.edit', $tariff->id) }}" class="btn btn-warning">Tahrirlash</a>
                                            <form action="{{ route('tariffs.destroy', $tariff->id) }}" method="POST"
                                                  style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-red">O‘chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $tariffs->links() }}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
