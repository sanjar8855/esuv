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

                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
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
                            <label for="role">Foydalanuvchi:</label>
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

                        <div class="mb-3">
                            <label for="rank">Lavozim:</label>
                            <input type="text" name="rank" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="files">Fayl:</label>
                            <input type="file" name="files" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <div class="mb-3">
                                <label class="form-label">Ishga kirgan Sana:</label>

                                <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-1"><path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                            d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path
                                            d="M12 15v3"/></svg>
                                </span>
                                    <input name="installation_date" class="form-control" placeholder="Sanani tanlang"
                                           id="datepicker-icon-prepend1"/>
                                </div>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        window.Litepicker && (new Litepicker({
                                            element: document.getElementById('datepicker-icon-prepend1'),
                                            format: 'YYYY-MM-DD',
                                            dropdowns: {
                                                minYear: 2010,  // Boshlang‘ich yil
                                                maxYear: new Date().getFullYear(),  // Hozirgi yildan keyingi 10 yilgacha
                                                months: true,  // Oynilar dropdownda chiqishi uchun
                                                years: true  // Yillarni dropdown shaklida chiqarish
                                            },
                                            buttonText: {
                                                previousMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                                                nextMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                                            },
                                        }));
                                    });
                                </script>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>
@endsection
