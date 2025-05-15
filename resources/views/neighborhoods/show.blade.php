@extends('layouts.app')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #streetsInNeighborhoodTable {
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
                    <h1>{{ $neighborhood->name }} mahallasi</h1>
                    <p>
                        <strong>Shahar:</strong>
                        @if($neighborhood->city)
                            <a href="{{ route('cities.show', $neighborhood->city->id) }}"
                               class="badge badge-outline text-blue">
                                {{ $neighborhood->city->name }}
                                @if($neighborhood->city->region)
                                    ({{ $neighborhood->city->region->name }})
                                @endif
                            </a>
                        @else
                            Belgilanmagan
                        @endif
                        <br>
                        <strong>Kompaniya:</strong>
                        @if($neighborhood->company)
                            <span class="badge bg-purple-lt">{{ $neighborhood->company->name }}</span>
                        @else
                            <span class="badge bg-secondary-lt">Kompaniyaga biriktirilmagan</span>
                        @endif
                    </p>
                    <div class="d-flex mb-3">
                        <a href="{{ route('neighborhoods.index') }}" class="btn btn-secondary">Ortga</a>
                        @hasrole('admin') {{-- Yoki can('update', $neighborhood) --}}
                        <a href="{{ route('neighborhoods.edit', $neighborhood->id) }}" class="btn btn-warning ms-2">Mahallani
                            Tahrirlash</a>
                        {{-- Ko'cha qo'shish tugmasi --}}
                        <a href="{{ route('streets.create', ['neighborhood_id' => $neighborhood->id, 'company_id' => $neighborhood->company_id]) }}"
                           class="btn btn-primary ms-auto">Yangi Ko‘cha Qo‘shish</a>
                        @endhasrole
                    </div>


                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Ko‘chalar Ro‘yxati ({{ $streetsCount }} ta)</h3>
                        </div>
                        <div class="table-responsive">
                            <table id="streetsInNeighborhoodTable"
                                   class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kompaniya</th> {{-- Har bir ko'cha uchun alohida kompaniya --}}
                                    <th>Ko‘cha Nomi</th>
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
            $('#streetsInNeighborhoodTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('neighborhoods.show', $neighborhood->id) }}", // AJAX so'rov shu sahifaga yuboriladi
                columns: [
                    {data: 'id_display', name: 'id'},
                    {data: 'company_name_display', name: 'company.name'}, // Kompaniya (saralash company.name bo'yicha)
                    {data: 'name', name: 'name'}, // Ko'cha nomi (kontrollerda editColumn('name') orqali formatlanadi)
                    {data: 'customer_count', name: 'customer_count', searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                order: [[1, 'asc']], // Boshlang'ich saralash: Ko'cha nomi bo'yicha
                pageLength: 25, // Yoki 50
                language: { /* ... o'zbekcha tarjimalar ... */
                    search: "Qidiruv:",
                    lengthMenu: "_MENU_ ta yozuv ko'rsatish",
                    info: "_TOTAL_ ta yozuvdan _START_ dan _END_ gachasi ko'rsatilmoqda",
                    infoEmpty: "Yozuvlar mavjud emas",
                    infoFiltered: "(_MAX_ ta yozuv ichidan filtrlandi)",
                    zeroRecords: "Mos yozuvlar topilmadi",
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
