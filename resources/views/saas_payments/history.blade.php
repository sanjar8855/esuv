@extends('layouts.app')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #customersTable {
        width: 100% !important;
    }

    /* TomSelect va DT filtrlari orasida joy tashlash */
    .filter-form {
        margin-bottom: 1rem;
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
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1>Barcha To'lovlar Tarixi (Jurnal)</h1>
                        <a href="{{ route('saas.payments.index') }}" class="btn btn-secondary">Oylar Bo'yicha Holatga Qaytish</a>
                    </div>

                    <div class="card">
                        <div class="table-responsive">
                            <table id="saasPaymentsHistoryTable" class="table table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Kompaniya Nomi</th>
                                    <th>To'lov Davri</th>
                                    <th>Summa</th>
                                    <th>To'lov Sanasi</th>
                                    <th>Kim Qo'shdi</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{-- Ma'lumotlar DataTables tomonidan AJAX orqali yuklanadi --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- jQuery va DataTables JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#saasPaymentsHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('saas.payments.history') }}", // Yangi marshrutga so'rov
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'company_name', name: 'company.name' },
                    { data: 'payment_period', name: 'payment_period' },
                    { data: 'amount', name: 'amount' },
                    { data: 'payment_date', name: 'payment_date' },
                    { data: 'created_by_user', name: 'createdBy.name' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[ 4, 'desc' ]], // Boshlang'ich saralash: To'lov sanasi bo'yicha (eng yangisi birinchi)
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
                },
                searchDelay: 500,
            });
        });
    </script>
@endpush
