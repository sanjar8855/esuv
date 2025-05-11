@extends('layouts.app') {{-- Asosiy layout faylingiz --}}

{{-- DataTables Bootstrap 5 CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* Jadval to'liq kenglikda bo'lishi uchun */
    #meterReadingsTable { width: 100% !important; }

    /* Qidiruv va "Nechta ko'rsatish" elementlarini standart o'lchamga keltirish */
    .dataTables_length select.form-select,
    .dataTables_filter input.form-control {
        height: calc(2.25rem + 2px); /* Bootstrap standart balandligi */
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
        padding-left: 0.75rem;
        font-size: 0.875rem; /* Bootstrap standart shrift o'lchami */
        line-height: 1.5;
    }

    /* Jadvaldagi rasmlar uchun stil */
    #meterReadingsTable img {
        max-width: 50px;  /* Maksimal kenglik */
        max-height: 50px; /* Maksimal balandlik */
        object-fit: cover; /* Rasm sig'masa, qirqib to'g'rilaydi */
        border-radius: 0.25rem; /* Burchaklarni yumaloqlash */
        border: 1px solid #dee2e6; /* Nozik chegara */
        vertical-align: middle; /* Boshqa kontent bilan tekislash */
    }
</style>

@section('content') {{-- Sahifa asosiy kontenti --}}
<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                {{-- Sarlavha va umumiy son --}}
                <h1>Hisoblagich O‘qishlari, {{ $meterReadingsCount }} ta</h1>
                {{-- Yangi qo'shish tugmasi --}}
                <a href="{{ route('meter_readings.create') }}" class="btn btn-primary mb-3">Yangi O‘qish Qo‘shish</a>

                {{-- Sessiya xabarlari (masalan, muvaffaqiyatli saqlangandan keyin) --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Asosiy jadval kartasi --}}
                <div class="card">
                    <div class="table-responsive">
                        {{-- Jadvalga ID berish muhim (JavaScript uchun) --}}
                        <table id="meterReadingsTable" class="table table-sm table-bordered table-vcenter card-table table-striped">
                            <thead>
                            <tr>
                                {{-- Jadval sarlavhalari --}}
                                <th>N</th> {{-- Tartib raqami --}}
                                <th>Mijoz</th>
                                <th>Hisoblagich Raqami</th>
                                <th>Ko'rsatgich raqami (m³)</th>
                                <th>O‘qish vaqti</th>
                                <th>Rasm</th>
                                <th>Holat</th>
                                <th>Amallar</th>
                            </tr>
                            </thead>
                            <tbody>
                            {{-- Bu qism bo'sh qoladi, DataTables AJAX orqali to'ldiradi --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Laravelning standart pagination linklari endi kerak emas --}}
                {{-- {{ $meterReadings->links() }} --}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts') {{-- JavaScript kodlarini layoutning <body> oxiriga qo'shish --}}
{{-- jQuery (DataTables uchun kerak) --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- DataTables asosiy JS --}}
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
{{-- DataTables Bootstrap 5 integratsiyasi JS --}}
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Sahifa to'liq yuklangandan keyin ishga tushadi
    $(document).ready(function() {
        // DataTables'ni #meterReadingsTable ID'li jadvalga qo'llaymiz
        $('#meterReadingsTable').DataTable({
            processing: true, // "Yuklanmoqda..." indikatorini ko'rsatish
            serverSide: true, // Ma'lumotlarni serverdan olishni yoqish
            ajax: {
                url: "{{ route('meter_readings.index') }}", // Ma'lumotlarni olish uchun Controller manzili
                type: "GET", // So'rov turi
                // Serverdan xatolik kelsa, uni ushlash (ixtiyoriy)
                // error: function (xhr, error, thrown) {
                //      console.error("DataTables AJAX xatosi:", xhr, error, thrown);
                //      // Foydalanuvchiga xabar berish mumkin
                //      alert('Jadval ma\'lumotlarini yuklashda xatolik yuz berdi. Iltimos, keyinroq qayta urinib ko\'ring.');
                //  }
            },
            columns: [
                // Har bir ustun uchun konfiguratsiya
                // 'data' - Controllerdan keladigan JSON javobidagi kalit nomi
                // 'name' - Server tomonida saralash/qidirish uchun ishlatiladigan nom (odatda bazadagi ustun yoki Controllerda belgilangan nom)
                // 'orderable' - shu ustun bo'yicha saralash mumkinmi
                // 'searchable' - shu ustun bo'yicha qidirish mumkinmi
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, // Tartib raqami
                { data: 'customer_link', name: 'customer_link', orderable: true, searchable: true }, // Mijoz (qidirish/saralash Controllerda sozlandi)
                { data: 'meter_link', name: 'meter_link', orderable: true, searchable: true }, // Hisoblagich (qidirish/saralash Controllerda sozlandi)
                { data: 'reading', name: 'reading', searchable: true }, // O'qish qiymati (qidirish mumkin)
                { data: 'created_at', name: 'created_at', searchable: false }, // O'qish sanasi (qidirish o'chirilgan)
                { data: 'photo_display', name: 'photo_display', orderable: false, searchable: false }, // Rasm (saralash/qidirish o'chirilgan)
                { data: 'status_badge', name: 'confirmed', searchable: true }, // Holat (qidirish 'confirmed' bo'yicha)
                { data: 'actions', name: 'actions', orderable: false, searchable: false } // Amallar (saralash/qidirish o'chirilgan)
            ],
            pageLength: 25,
            order: [[0, 'desc']], // Boshlang'ich saralash (masalan, ID bo'yicha kamayish tartibida)
            language: { // O'zbekcha tarjima (qo'lda kiritilgan)
                search: "Qidiruv:",
                lengthMenu: "_MENU_ ta yozuv ko'rsatish",
                info: "_TOTAL_ ta yozuvdan _START_ dan _END_ gachasi ko'rsatilmoqda",
                infoEmpty: "Yozuvlar mavjud emas",
                infoFiltered: "(_MAX_ ta yozuv ichidan filtrlandi)",
                zeroRecords: "Hech qanday mos yozuv topilmadi",
                emptyTable: "Jadvalda ma'lumotlar mavjud emas",
                processing: '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Yuklanmoqda...', // Bootstrap spinner bilan chiroyliroq
                paginate: {
                    first: "Birinchi",
                    last: "Oxirgi",
                    next: "Keyingi",
                    previous: "Oldingi"
                },
                aria: {
                    sortAscending: ": ustunni o'sish tartibida saralash uchun aktivlashtiring",
                    sortDescending: ": ustunni kamayish tartibida saralash uchun aktivlashtiring"
                }
            }
            // Boshqa DataTables sozlamalari (masalan, qidiruv uchun kechikish)
            // searchDelay: 500
        });
    });
</script>
@endpush
