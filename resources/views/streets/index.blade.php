@extends('layouts.app')

{{-- DataTables CSS (Agar layoutda bo'lmasa) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* DataTables stillari */
    #streetsTable { width: 100% !important; }
    .dataTables_length select.form-select,
    .dataTables_filter input.form-control {
        height: calc(2.25rem + 2px); padding-top: 0.375rem; padding-bottom: 0.375rem;
        padding-left: 0.75rem; font-size: 0.875rem; line-height: 1.5;
    }
    /* Qarzdorlik uchun ranglar */
    .total-debt-negative { color: red; font-weight: bold; }
    .total-debt-zero { color: grey; }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Ko'chalar</h1>
                    @hasrole('admin')
                    <a href="{{ route('streets.create') }}" class="btn btn-primary mb-3">Yangi Ko'cha Qo'shish</a>
                    @endhasrole

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table id="streetsTable" class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kompaniya</th>
                                    <th>Mahalla</th>
                                    <th>Ko‘cha Nomi</th>
                                    <th>Mijozlar soni</th>
                                    <th>Jami qarzdorlik (UZS)</th>
                                    <th>Harakatlar</th>
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
    {{-- DataTables JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#streetsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('streets.index') }}",
                columns: [
                    { data: 'id_display', name: 'streets.id' },
                    { data: 'company_name_display', name: 'companies.name' },
                    { data: 'neighborhood_full_path_display', name: 'neighborhoods.name'},
                    { data: 'name', name: 'streets.name' }, // Jadval nomini aniq ko'rsatish
                    { data: 'customer_count', name: 'customer_count', searchable: false, orderable: true },
                    { data: 'total_debt', name: 'total_debt_val', searchable: false, orderable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                // Boshlang'ich saralash: Mahalla, keyin Ko'cha
                order: [[1, 'asc'], [2, 'asc']],
                pageLength: 50,
                language: { // O'zbekcha tarjima (users jadvalidagi kabi)
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
                    }
                }
            });
        });
    </script>
@endpush
