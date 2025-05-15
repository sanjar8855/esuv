@extends('layouts.app')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #citiesTable {
        width: 100% !important;
    }

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
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    {{-- Jami sonni DataTables o'zi chiqaradi, sarlavhadan olib tashladim --}}
                    <h1>Shaharlar</h1>
                    {{-- Bu sahifaga faqat admin kiradi deb hisoblaymiz --}}
                    <a href="{{ route('cities.create') }}" class="btn btn-primary mb-3">Yangi Shahar Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table id="citiesTable"
                                   class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Kompaniya</th> {{-- Yangi ustun --}}
                                    <th>Viloyat</th>
                                    <th>Shahar/Tuman Nomi</th>
                                    <th>Mahallalar soni</th>
                                    <th>Mijozlar soni</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{-- Ma'lumotlar DataTables tomonidan AJAX orqali yuklanadi --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Laravel pagination olib tashlandi --}}
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
            $('#citiesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('cities.index') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false}, // N
                    {data: 'company_name_display', name: 'company.name'}, // Kompaniya (saralash 'company.name' bo'yicha)
                    {data: 'region_name', name: 'region.name'}, // Viloyat (saralash 'region.name' bo'yicha)
                    {data: 'name', name: 'name'}, // Shahar Nomi (saralash 'cities.name' bo'lishi kerak, 'name' ham ishlaydi)
                    {data: 'neighborhood_count', name: 'neighborhood_count_val', searchable: false}, // Mahalla soni (saralash 'neighborhoods_count' yoki 'neighborhood_count_val' bo'yicha)
                    {data: 'customer_count', name: 'customer_count_val', searchable: false}, // Mijozlar soni (saralash 'customer_count_val' bo'yicha)
                    {data: 'actions', name: 'actions', orderable: false, searchable: false} // Amallar
                ],
                // Boshlang'ich saralash: Viloyat, keyin Shahar nomi bo'yicha
                order: [[1, 'asc'], [2, 'asc']],
                pageLength: 25, // Talab bo'yicha
                language: { // O'zbekcha tarjima
                    "search": "Qidiruv:",
                    "lengthMenu": "_MENU_ tadan ko'rsatish",
                    "info": "_TOTAL_ yozuvdan _START_ dan _END_ gachasi ko'rsatilmoqda",
                    "infoEmpty": "Yozuvlar mavjud emas",
                    "infoFiltered": "(_MAX_ yozuv ichidan filtrlandi)",
                    "zeroRecords": "Mos yozuvlar topilmadi",
                    "emptyTable": "Jadvalda ma'lumotlar mavjud emas",
                    "processing": "Yuklanmoqda...",
                    "paginate": {
                        "first": "Birinchi",
                        "last": "Oxirgi",
                        "next": "Keyingi",
                        "previous": "Oldingi"
                    }
                }
            });
        });
    </script>
@endpush
