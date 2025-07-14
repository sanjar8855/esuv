@extends('layouts.app') {{-- Yoki sizning layout faylingiz --}}

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
{{-- Qo'shimcha DataTables kengaytmalari (Buttons, Responsive) CSS'lari kerak bo'lsa shu yerga qo'shiladi --}}
<style>
    /* Zarur bo'lsa, jadval uchun qo'shimcha stillar */
    #usersTable {
        width: 100% !important;
    }
    .dataTables_length select.form-select,
    .dataTables_filter input.form-control {
        height: calc(2.25rem + 2px); /* Bootstrap standart balandligi */
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
        padding-left: 0.75rem;
        font-size: 0.875rem; /* Bootstrap standart shrift o'lchami */
        line-height: 1.5;
    }
    /* Kenglikni to'liq egallash uchun */
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>
                        Xodimlar, {{ $usersCount }}</h1> {{-- Sonni DataTables o'zi ko'rsatadi yoki JS bilan yangilasa bo'ladi --}}
                    {{-- Yoki boshlang'ich sonni ko'rsatish: <h1>Xodimlar, {{ $usersCount }} ta</h1> --}}
                    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Yangi xodim qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            {{-- Jadvalga ID beramiz va tbody ni bo'sh qoldiramiz --}}
                            <table id="usersTable" class="table table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kompaniya</th>
                                    <th>Ism</th>
                                    <th>Foydalanuvchi turi</th>
                                    <th>Email</th>
                                    <th>Lavozim</th>
                                    <th>Telefon raqam</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{-- Ma'lumotlar DataTables tomonidan AJAX orqali yuklanadi --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Laravel pagination linklarini olib tashlaymiz: {{ $users->links() }} --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    {{-- Qo'shimcha DataTables kengaytmalari (Buttons, Responsive) JS'lari kerak bo'lsa shu yerga qo'shiladi --}}

    <script>
        $(document).ready(function () {
            // DataTables'ni #usersTable ID'li jadvalga qo'llaymiz
            $('#usersTable').DataTable({
                processing: true, // Ishlov jarayoni indikatorini ko'rsatish
                serverSide: true, // Server tomonida ishlashni yoqish
                ajax: {
                    url: "{{ route('users.index') }}", // Ma'lumotlarni olish uchun Controller'dagi route
                    type: 'GET' // So'rov turi (Controller'dagi route metodi bilan bir xil bo'lishi kerak)
                },
                columns: [
                    // `data` - Controller'dan keladigan JSON javobidagi kalit nomi
                    // `name` - Server tomonida saralash/qidirish uchun ishlatiladigan ustun nomi (odatda bazadagi nom)
                    {data: 'id', name: 'users.id'},
                    {data: 'company_name', name: 'companies.name', orderable: true, searchable: true},
                    {data: 'name', name: 'users.name'},
                    {data: 'roles', name: 'roles', orderable: false, searchable: false}, // Controller'da `addColumn` bilan qo'shilgan
                    {data: 'email', name: 'email'},
                    {data: 'rank', name: 'rank'}, // User modelida 'rank' maydoni borligiga ishonch hosil qiling
                    {data: 'phone', name: 'phone', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false} // Controller'da `addColumn` bilan qo'shilgan
                ],
                order: [[0, 'desc']], // Boshlang'ich saralash (masalan, ID bo'yicha kamayish tartibida)
                pageLength: 25,
                language: {
                    search: "Qidiruv:",
                    lengthMenu: "_MENU_ ta yozuv ko'rsatish",
                    info: "_TOTAL_ ta yozuvdan _START_ dan _END_ gachasi ko'rsatilmoqda",
                    infoEmpty: "Yozuvlar mavjud emas",
                    infoFiltered: "(_MAX_ ta yozuv ichidan filtrlandi)",
                    zeroRecords: "Hech qanday mos yozuv topilmadi",
                    emptyTable: "Jadvalda ma'lumotlar mavjud emas",
                    processing: "Yuklanmoqda...",
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
