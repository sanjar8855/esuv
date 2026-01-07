@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Mijozlarni Exceldan Import Qilish</h1>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon alert-icon">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="alert-title">Xatoliklar topildi!</h4>
                                    <div class="text-muted">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon alert-icon">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path d="M9 12l2 2l4 -4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="alert-title">Muvaffaqiyatli!</h4>
                                    <div class="text-muted">{{ session('success') }}</div>
                                </div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon alert-icon">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v2m0 4v.01"></path>
                                        <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="alert-title">Ogohlantirish!</h4>
                                    <div class="text-muted">
                                        {{ session('warning') }}
                                        @if (session('import_log_id'))
                                            <br>
                                            <a href="{{ route('import_logs.show', session('import_log_id')) }}" class="btn btn-warning btn-sm mt-2">
                                                Xatoliklarni batafsil ko'rish
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon alert-icon">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="alert-title">Xato!</h4>
                                    <div class="text-muted">
                                        {{ session('error') }}
                                        @if (session('import_log_id'))
                                            <br>
                                            <a href="{{ route('import_logs.show', session('import_log_id')) }}" class="btn btn-danger btn-sm mt-2">
                                                Xatoliklarni batafsil ko'rish
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif

                    {{-- Import Log linklari --}}
                    <div class="mb-3">
                        <a href="{{ route('import_logs.index') }}" class="btn btn-outline-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"></path>
                                <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"></path>
                                <line x1="3" y1="6" x2="3" y2="19"></line>
                                <line x1="12" y1="6" x2="12" y2="19"></line>
                                <line x1="21" y1="6" x2="21" y2="19"></line>
                            </svg>
                            Import Loglarini Ko'rish
                        </a>
                    </div>

                    {{-- ============ IMPORT FORMALARI ============ --}}
                    <div class="row row-cards mt-3">
                        {{-- ============ 1. MEYORIY MIJOZLAR ============ --}}
                        <div class="col-lg-6">
                            <form action="{{ route('customers.import.handle.no_meter') }}" method="POST"
                                  enctype="multipart/form-data">
                                <h1>Meyoriy</h1>
                                @csrf
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="excel_file" class="form-label">Excel Faylni Tanlang (.xlsx,
                                                .xls, .csv)</label>
                                            <input type="file" name="excel_file" id="excel_file_no_meter"
                                                   class="form-control" required>
                                            <small class="form-hint">
                                                <a href="#" download>Shablonni yuklab oling.</a> <br>
                                                <b>Majburiy ustunlar:</b> kompaniya_id, kocha_id, fio, hisob_raqam,
                                                oila_azolari.
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary">Import Qilish</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- ============ 2. HISOBLAGICHLI MIJOZLAR ============ --}}
                        <div class="col-lg-6">
                            <form action="{{ route('customers.import.handle.with_meter') }}" method="POST"
                                  enctype="multipart/form-data">
                                <h1>Hisoblagichi borlar</h1>
                                @csrf
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="excel_file" class="form-label">Excel Faylni Tanlang (.xlsx,
                                                .xls, .csv)</label>
                                            <input type="file" name="excel_file" id="excel_file_with_meter"
                                                   class="form-control" required>
                                            <small class="form-hint">
                                                Fayl shablonini <a href="/path/to/your/template.xlsx" download>shu
                                                    yerdan</a> yuklab oling. <br>
                                                <b>Majburiy ustunlar:</b> kompaniya_id, kocha_id, fio, hisob_raqam,
                                                boshlangich_korsatkich, korsatkich_sanasi<br>
                                                <b>Ixtiyoriy:</b> telefon_raqami, uy_raqami, oila_azolari,
                                                hisoblagich_ornatilgan_sana, amal_qilish_muddati, oxirgi_korsatkich (yoki songi_korsatkich)
                                                <br><br>
                                                <b>Ko'rsatkichlar logikasi:</b>
                                                <ul class="mb-0">
                                                    <li>Agar faqat <b>boshlangich_korsatkich</b> berilsa → u saqlanadi, balans = 0</li>
                                                    <li>Agar <b>boshlangich > oxirgi</b> → boshlangichni saqla, balans = 0</li>
                                                    <li>Agar <b>oxirgi > boshlangich</b> → oxirgi saqlanadi, balans = -(farq × tarif) (qarz)</li>
                                                    <li>Agar <b>boshlangich = oxirgi</b> → boshlangichni saqla, balans = 0</li>
                                                </ul>
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
        </div>
@endsection
