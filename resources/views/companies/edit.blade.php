@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">

                    <h2>Kompaniyani tahrirlash</h2>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('companies.update', $company->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nomi</label>
                            <input type="text" name="name" class="form-control" value="{{ $company->name }}" required>
                        </div>

{{--                        <div class="mb-3">--}}
{{--                            <label class="form-label">Email</label>--}}
{{--                            <input type="email" name="email" class="form-control" value="{{ $company->email }}" required>--}}
{{--                        </div>--}}

                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="phone" class="form-control" value="{{ $company->phone }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="plan_id">Tarif Rejasi:</label>
                            <select name="plan_id" id="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                                <option value="">-- Tarifni Tanlang --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id', $company->plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} ({{ number_format($plan->price, 0, '', ' ') }} UZS/oy)
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Manzil</label>
                            <input type="text" name="address" class="form-control" value="{{ $company->address }}">
                        </div>

                        <div class="mb-3">
                            <label for="logo">Logo:</label>
                            <input type="file" name="logo" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hisob raqam:</label>
                            <input type="number" name="schet" value="{{$company->schet}}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">INN:</label>
                            <input type="number" name="inn" value="{{$company->inn}}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Izoh:</label>
                            <textarea name="description" class="form-control">{{$company->description}}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1"
                                    {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">Kompaniya faolmi?</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                        <a href="{{ route('companies.index') }}" class="btn btn-secondary">Bekor qilish</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
