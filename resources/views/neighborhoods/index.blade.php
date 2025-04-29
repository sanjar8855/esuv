@extends('layouts.app')

{{-- DataTables CSS (Agar layoutda bo'lmasa) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* DataTables elementlari uchun Bootstrap 5 stillari */
    #neighborhoodsTable { width: 100% !important; }
    .dataTables_length select.form-select,
    .dataTables_filter input.form-control {
        height: calc(2.25rem + 2px); padding-top: 0.375rem; padding-bottom: 0.375rem;
        padding-left: 0.75rem; font-size: 0.875rem; line-height: 1.5;
    }
    /* Balans uchun ranglar (kerak bo'lsa) */
    .total-debt-negative { color: red; font-weight: bold; }
    .total-debt-positive { color: green; } /* Agar musbat balans ham bo'lsa */
    .total-debt-zero { color: grey; }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Mahallalar</h1>
                    <a href="{{ route('neighborhoods.create') }}" class="btn btn-primary mb-3">Yangi Mahalla Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            {{-- Jadvalga ID beramiz --}}
                            <table id="neighborhoodsTable" class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th> {{-- N o'rniga ID --}}
                                    <th>Shahar</th>
                                    <th>Mahalla Nomi</th>
                                    <th>Ko‘chalar soni</th>
                                    <th>Jami qarzdorlik</th> {{-- YANGI USTUN --}}
                                    <th>Harakatlar</th>
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
    {{-- DataTables JS (jQuery ham kerak, agar layoutda bo'lmasa) --}}
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
                    { data: 'id', name: 'id' },
                    // `data: 'city'` - Controllerdagi addColumn nomi
                    // `name: 'cities.name'` - Saralash/qidirish uchun bazadagi ustun (join qilingan)
                    { data: 'city', name: 'cities.name' },
                    { data: 'name', name: 'neighborhoods.name' }, // To'liq nom berish yaxshiroq
                    { data: 'street_count', name: 'street_count', searchable: false },
                    { data: 'total_debt', name: 'total_debt', searchable: false, orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'asc'], [2, 'asc']], // cities.name, keyin neighborhoods.name bo'yicha
                language: { /* ... tarjima ... */ }
            });
        });
    </script>
@endpush
