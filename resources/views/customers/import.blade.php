@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Mijozlarni Exceldan Import Qilish</h1>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Xatoliklar:</strong><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('customers.import.handle') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="excel_file" class="form-label">Excel Faylni Tanlang (.xlsx, .xls, .csv)</label>
                                    <input type="file" name="excel_file" id="excel_file" class="form-control" required>
                                    <small class="form-hint">
                                        Fayl shablonini <a href="/path/to/your/template.xlsx" download>shu yerdan</a> yuklab oling. <br>
                                        Ustunlar: kompaniya_id, kocha_id, fio, telefon_raqami, uy_raqami, hisob_raqam, oila_azolari, hisoblagich_bormi (1/0), hisoblagich_ornatilgan_sana, amal_qilish_muddati, boshlangich_korsatkich, korsatkich_sanasi.
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Import Qilish</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
