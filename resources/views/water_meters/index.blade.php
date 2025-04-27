@extends('layouts.app')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* Jadval kengligini to'liq qilish */
    #waterMetersTable {
        width: 100% !important;
    }
    /* ---- Length Menu Select'ni kattalashtirish ---- */
    .dataTables_length select.form-select {
        height: calc(2.25rem + 2px); /* Yoki kerakli balandlik */
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
        padding-left: 0.75rem;
        font-size: 0.875rem; /* Yoki standart shrift o'lchami */
        line-height: 1.5; /* Standart qator balandligi */
    }
    .dataTables_filter input.form-control {
        height: calc(2.25rem + 2px); /* Yoki kerakli balandlik */
        padding: 0.375rem 0.75rem;  /* Standart padding */
        font-size: 0.875rem;         /* Yoki standart shrift o'lchami */
        line-height: 1.5;
    }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    {{-- Umumiy sonni ko'rsatish --}}
                    <h1>Hisoblagichlar, {{ $waterMetersCount }} ta</h1>
                    <a href="{{ route('water_meters.create') }}" class="btn btn-primary mb-3">Yangi Hisoblagich
                        Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            {{-- Jadvalga ID beramiz --}}
                            <table id="waterMetersTable"
                                   class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    {{-- Ustun nomlari --}}
                                    <th>N</th>
                                    <th>Mijoz</th>
                                    <th>Hisoblagich Raqami</th>
                                    <th>O‘rnatilgan Sana</th>
                                    <th>Amal Muddati (yil)</th>
                                    <th>Tugash Sanasi</th>
                                    <th>Oxirgi Ko‘rsatkich</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{-- DataTables bu yerni to'ldiradi --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Laravel pagination o'chirildi --}}
                    {{-- {{ $waterMeters->links() }} --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // DataTables'ni ishga tushirish
            $('#waterMetersTable').DataTable({
                processing: true, // "Ishlanmoqda..." xabarini ko'rsatish
                serverSide: true, // Server tomonida ishlashni yoqish
                ajax: {
                    url: "{{ route('water_meters.index') }}", // Controller'ga so'rov yuborish manzili
                    type: "GET"
                },
                columns: [
                    // Controller'dan keladigan JSON javobidagi kalitlarga mos 'data'
                    // 'name' - serverda saralash/qidirish uchun ishlatiladi
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false}, // Tartib raqami
                    {data: 'customer_link', name: 'customer_link', orderable: true, searchable: true}, // Mijoz (saralash/qidirish Controllerda)
                    {data: 'meter_number', name: 'meter_number'}, // Hisoblagich raqami
                    {data: 'installation_date', name: 'installation_date'}, // O'rnatilgan sana
                    {data: 'validity_period', name: 'validity_period'}, // Amal qilish muddati
                    {data: 'expiration_date', name: 'expiration_date'}, // Tugash sanasi
                    {data: 'last_reading_value', name: 'last_reading_value', orderable: false, searchable: false}, // Oxirgi ko'rsatkich
                    {data: 'actions', name: 'actions', orderable: false, searchable: false} // Amallar
                ],
                order: [[2, 'asc']], // Boshlang'ich saralash (masalan, hisoblagich raqami bo'yicha)
                language: { // <-- O'zbekcha tarjima (qo'lda kiritilgan)
                    search: "Qidiruv:",
                    lengthMenu: "_MENU_ ta yozuv ko'rsatish",
                    info: "_TOTAL_ ta yozuvdan _START_ dan _END_ gachasi ko'rsatilmoqda",
                    infoEmpty: "Yozuvlar mavjud emas",
                    infoFiltered: "(_MAX_ ta yozuv ichidan filtrlandi)",
                    zeroRecords: "Hech qanday mos yozuv topilmadi",
                    emptyTable: "Jadvalda ma'lumotlar mavjud emas",
                    processing: '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Yuklanmoqda...', // Bootstrap spinner bilan
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
            });
        });
    </script>
@endpush
