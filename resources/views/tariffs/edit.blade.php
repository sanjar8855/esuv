@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="container">
                        <h2>Tarifni Tahrirlash</h2>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('tariffs.update', $tariff->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Kompaniya</label>
                                @if(auth()->user()->hasRole('admin'))
                                    <select name="company_id" id="company_id" class="form-select">
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ $tariff->company_id == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="company_id" value="{{ auth()->user()->company->id }}">
                                    <input type="text" class="form-control" value="{{ auth()->user()->company->name }}" readonly>
                                @endif
                            </div>


{{--                            <div class="mb-3">--}}
{{--                                <label>Tarif nomi</label>--}}
{{--                                <input type="text" name="name" class="form-control" value="{{ $tariff->name }}" required>--}}
{{--                            </div>--}}

                            <div class="mb-3">
                                <label>1m³ narxi (UZS)</label>
                                <input type="number" step="1" name="price_per_m3" class="form-control" value="{{ $tariff->price_per_m3 }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Bir kishiga (UZS)</label>
                                <input type="number" step="1" name="for_one_person" class="form-control" value="{{ $tariff->price_per_m3 }}" required>
                            </div>

                            <div class="mb-3">
                                <label>Boshlanish sanasi</label>
                                <input type="date" name="valid_from" class="form-control" value="{{ $tariff->valid_from }}" required>
                            </div>

                            <div class="mb-3">
                                <label>Tugash sanasi</label>
                                <input type="date" name="valid_to" class="form-control" value="{{ $tariff->valid_to }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $tariff->is_active ? 'checked' : '' }}>
                                    <span class="form-check-label">Faol tarif</span>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-success">Saqlash</button>
                            <a href="{{ route('tariffs.index') }}" class="btn btn-secondary">Bekor qilish</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
