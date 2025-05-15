@extends('layouts.app')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #neighborhoodsTable {
        width: 100% !important;
    }

    /* ID to'g'ri bo'lishi kerak */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        height: calc(2.25rem + 2px);
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
        padding-left: 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: var(--tblr-border-radius);
    }

    .text-danger.fw-bold {
        color: red !important;
        font-weight: bold !important;
    }

    .text-muted { /* kerak bo'lsa */
    }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Mahallalar</h1>
                    <a href="{{ route('neighborhoods.create') }}" class="btn btn-primary mb-3">Yangi Mahalla
                        Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table id="neighborhoodsTable"
                                   class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kompaniya</th> {{-- Yangi ustun --}}
                                    <th>Shahar, Viloyat</th> {{-- Sarlavha o'zgartirildi --}}
                                    <th>Mahalla Nomi</th>
                                    <th>Ko‘chalar soni</th>
                                    <th>Mijozlar soni</th>
                                    <th>Jami qarzdorlik (UZS)</th>
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
            $('#neighborhoodsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('neighborhoods.index') }}",
                columns: [
                    {data: 'id', name: 'id'}, // ID
                    {data: 'company_name_display', name: 'company.name'}, // Kompaniya (saralash company.name bo'yicha)
                    {data: 'city_full_path', name: 'city.name'}, // Shahar, Viloyat (saralash city.name bo'yicha)
                    {data: 'name', name: 'name'}, // Mahalla Nomi (saralash neighborhoods.name bo'lishi kerak)
                    {data: 'street_count', name: 'street_count', searchable: false, orderable: true}, // Ko'chalar soni
                    {data: 'customer_count', name: 'customer_count_val', searchable: false, orderable: true}, // Mijozlar soni
                    {
                        data: 'total_debt_display',
                        name: 'total_debt_on_neighborhood',
                        searchable: false,
                        orderable: true
                    }, // Jami qarzdorlik
                    {data: 'actions', name: 'actions', orderable: false, searchable: false} // Amallar
                ],
                // Boshlang'ich saralash: Shahar, keyin Mahalla
                order: [[1, 'asc'], [2, 'asc']],
                pageLength: 25, // Yoki 50
                language: { /* ... o'zbekcha tarjimalar avvalgidek ... */
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
