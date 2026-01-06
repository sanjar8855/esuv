@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Hisoblagich Ko'rsatkichlarini Exceldan Import Qilish</h1>

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
                                                Xatoliklarni ko'rish
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
                                                Xatoliklarni ko'rish
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

                    {{-- ============ IMPORT FORMASI ============ --}}
                    <div class="row row-cards mt-3">
                        <div class="col-lg-8">
                            <form action="{{ route('meter_readings.import') }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Ko'rsatkichlarni Import Qilish</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="excel_file" class="form-label">Excel Faylni Tanlang (.xlsx,
                                                .xls, .csv)</label>
                                            <input type="file" name="excel_file" id="excel_file"
                                                   class="form-control" required accept=".xlsx,.xls,.csv">
                                            <small class="form-hint">
                                                <b>Majburiy ustunlar:</b>
                                                <ul class="mb-0">
                                                    <li><b>hisob_raqam</b> - Hisoblagich raqami (majburiy)</li>
                                                    <li><b>boshlangich_korsatkich</b> - Boshlang'ich ko'rsatkich (majburiy)</li>
                                                    <li><b>oxirgi_korsatkich</b> - Oxirgi ko'rsatkich (ixtiyoriy)</li>
                                                    <li><b>korsatkich_sanasi</b> - Ko'rsatkich sanasi (majburiy)</li>
                                                </ul>
                                                <br>
                                                <b>Muhim eslatmalar:</b>
                                                <ul class="mb-0">
                                                    <li>Agar hisob raqam topilmasa, qator o'tkazib yuboriladi va xatolar logiga yoziladi</li>
                                                    <li>Faqat boshlang'ich ko'rsatkich berilsa, u saqlanadi</li>
                                                    <li>Agar boshlang'ich == oxirgi bo'lsa, qarz 0 hisoblanadi</li>
                                                    <li>Agar oxirgi > boshlang'ich bo'lsa, oxirgi ko'rsatkich saqlanadi</li>
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

                        {{-- Yordamchi ma'lumot --}}
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Qanday ishlaydi?</h3>
                                </div>
                                <div class="card-body">
                                    <ol class="mb-0">
                                        <li>Excel faylni tayyorlang (yuqoridagi formatda)</li>
                                        <li>Faylni yuklang</li>
                                        <li>Sistema har bir qatorni tekshiradi</li>
                                        <li>Xato bo'lgan qatorlar logga yoziladi</li>
                                        <li>To'g'ri qatorlar import qilinadi</li>
                                        <li>Natija va xatoliklarni ko'ring</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
