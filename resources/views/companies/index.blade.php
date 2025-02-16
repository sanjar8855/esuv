@extends('layouts.app')

@section('content')

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Kompaniyalar</h2>
                    <a href="{{ route('companies.create') }}" class="btn btn-primary">Yangi kompaniya qo‘shish</a>
                    <br>
                    <br>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Nomi</th>
                                    <th>Email</th>
                                    <th>Telefon</th>
                                    <th>Plan</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($companies as $company)
                                    <tr>
                                        <td>{{$loop->index +1}}</td>
                                        <td class="text-secondary">
                                            {{ $company->name }}
                                            @if($company->is_active)
                                                <span class="badge bg-cyan text-cyan-fg">Faol</span>
                                            @else
                                                <span class="badge bg-red text-red-fg">Nofaol</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary">{{ $company->email }}</td>
                                        <td class="text-secondary">{{ $company->phone }}</td>
                                        <td class="text-secondary">{{ $company->plan }}</td>
                                        <td>
                                            <a href="{{ route('companies.show', $company->id) }}" class="btn btn-info">Ko‘rish</a>
                                            <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-warning">Tahrirlash</a>
                                            <form action="{{ route('companies.destroy', $company->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Haqiqatan ham o‘chirilsinmi?')">O‘chirish
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
