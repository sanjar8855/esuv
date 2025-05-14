@extends('layouts.app')

{{-- DataTables CSS (Agar layoutda bo'lmasa) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* DataTables elementlari uchun Bootstrap 5 stillari */
    #streetsTable { width: 100% !important; }
    .dataTables_length select.form-select,
    .dataTables_filter input.form-control {
        height: calc(2.25rem + 2px); padding-top: 0.375rem; padding-bottom: 0.375rem;
        padding-left: 0.75rem; font-size: 0.875rem; line-height: 1.5;
    }
    /* Balans uchun ranglar */
    .total-debt-negative { color: red; font-weight: bold; }
    .total-debt-zero { color: grey; }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    {{-- Mahalla haqida ma'lumot --}}
                    <h1>Mahalla Tafsilotlari</h1>
                    <p>
                        <strong>Shahar:</strong>
                        <a href="{{ route('cities.show', $neighborhood->city->id) }}" class="badge badge-outline text-blue">
                            {{ $neighborhood->city->name }}
                        </a>
                    </p>
                    <p><strong>Mahalla Nomi:</strong> {{ $neighborhood->name }}</p>

                    <a href="{{ route('neighborhoods.index') }}" class="btn btn-secondary mb-3">Ortga</a>

                    {{-- Ko'chalar jadvali --}}
                    <h2>{{ $neighborhood->name }} mahallasidagi ko‘chalar
                        {{-- Agar sarlavhada ham son kerak bo'lsa: ({{ $streetsCount ?? 'N/A' }} ta) --}}
                    </h2>

                    <div class="card">
                        <div class="table-responsive">
                            {{-- Ko'chalar jadvaliga ID beramiz --}}
                            <table id="streetsTable" class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ko‘cha nomi</th>
                                    <th>Mijozlar soni</th>
                                    <th>Harakatlar</th>
                                </tr>
                                </thead>
                                <tbody>
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
    {{-- DataTables JS (jQuery ham kerak) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#streetsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('neighborhoods.show', $neighborhood->id) }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'customer_count', name: 'customer_count', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'asc']], // Boshlang'ich saralash
                pageLength: 25,
                language: { // O'zbekcha tarjima
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
