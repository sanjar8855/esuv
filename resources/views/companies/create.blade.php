@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Yangi kompaniya qo‘shish</h2>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('companies.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nomi</label>
                            <input type="text" name="name" class="form-control" value="{{old('name')}}" required>
                        </div>
{{--                        <div class="mb-3">--}}
{{--                            <label class="form-label">Email</label>--}}
{{--                            <input type="email" name="email" class="form-control" value="{{old('email')}}" required>--}}
{{--                        </div>--}}
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="phone" class="form-control" value="{{old('phone')}}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select name="plan" id="plan" class="form-control" >
                                <option value="basic">Standart</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Manzil</label>
                            <input type="text" name="address" value="{{old('address')}}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    {{ old('is_active') ? 'checked' : '' }}>
                                <span class="form-check-label">Kompaniya faolmi?</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
