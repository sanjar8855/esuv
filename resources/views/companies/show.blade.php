@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>{{ $company->name }} kompaniyasi</h2>

                    <table class="table">
                        <tr>
                            <th>Nomi:</th>
                            <td>{{ $company->name }}</td>
                        </tr>
{{--                        <tr>--}}
{{--                            <th>Email:</th>--}}
{{--                            <td>{{ $company->email }}</td>--}}
{{--                        </tr>--}}
                        <tr>
                            <th>Telefon:</th>
                            <td>{{ $company->phone }}</td>
                        </tr>
                        <tr>
                            <th>Plan:</th>
                            <td>{{ $company->plan }}</td>
                        </tr>
                        <tr>
                            <th>Manzil:</th>
                            <td>{{ $company->address ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>Hisob raqam:</th>
                            <td>{{ $company->schet ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>INN:</th>
                            <td>{{ $company->inn ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>Izoh:</th>
                            <td>{{ $company->description ?? 'Ko‘rsatilmagan' }}</td>
                        </tr>
                        <tr>
                            <th>Logo:</th>
                            <td>
                                <img src="{{ asset('tabler/img/hero/'.$company->logo) }}" alt="">
                            </td>
                        </tr>
                        <tr>
                            <th>Holati:</th>
                            <td>
                                @if($company->is_active)
                                    <span class="badge bg-cyan text-cyan-fg">Faol</span>
                                @else
                                    <span class="badge bg-red  text-red-fg">Faol emas</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <a href="{{ route('companies.index') }}" class="btn btn-secondary">Orqaga</a>
                    <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-warning">Tahrirlash</a>

                    <form action="{{ route('companies.destroy', $company->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Haqiqatan ham o‘chirilsinmi?')">O‘chirish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
