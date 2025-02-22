@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Yangi xodim Qo‘shish</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="company_id">Kompaniya:</label>
                            <select name="company_id" class="form-control" required>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name">Ism:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="role">Lavozim:</label>
                            <select name="role" id="role" class="form-select">
                                @if(auth()->user()->hasRole('admin'))
                                    <option value="company_owner">Direktor</option>
                                @endif
                                <option value="employee">Ishchi xodim</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="email">Email:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password">Parol:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
