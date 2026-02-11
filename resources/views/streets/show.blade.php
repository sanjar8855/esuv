@extends('layouts.app')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .balance-positive { color: green; }
    .balance-negative { color: red; font-weight: bold; }
    .balance-zero     { color: grey; }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <h1>Ko‘cha: {{ $street->name }}</h1>
            <p>
                <strong>Mahalla:</strong>
                <a href="{{ route('neighborhoods.show', $street->neighborhood->id) }}"
                   class="badge badge-outline text-blue">
                    {{ $street->neighborhood->name }}
                </a>
            </p>
            <p><strong>Faol mijozlar soni:</strong> {{ $customersCount }}</p>
            <a href="{{ route('streets.index') }}" class="btn btn-secondary mb-3">Ortga</a>

            <div class="card">
                <div class="table-responsive">
                    <table id="customersTable" class="table table-sm table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kompaniya</th>
                            <th>Manzil</th>
                            <th>Ism</th>
                            <th>Telefon</th>
                            <th>Hisoblagich</th>
                            <th>Balans</th>
                            <th>Oxirgi ko‘rsatkich</th>
                            <th>Amallar</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function(){
            $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('streets.show', $street->id) }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'company',      name: 'company.name',    orderable: true, searchable: true },
                    { data: 'address',      name: 'address' },
                    { data: 'name',         name: 'name' },
                    { data: 'phone',        name: 'phone' },
                    { data: 'meter',        name: 'waterMeter.meter_number', orderable:false, searchable:false },
                    { data: 'balance',      name: 'balance', orderable:true, searchable:false },
                    { data: 'last_reading', name: 'last_reading', orderable:false, searchable:false },
                    { data: 'actions',      name: 'actions', orderable:false, searchable:false },
                ],
                order: [[0,'asc']],
                pageLength: 50,
                language: {
                    search: "Qidiruv:",
                    lengthMenu: "_MENU_ ta yozuv",
                    info: "_TOTAL_ yozuvdan _START_–_END_ ko‘rinmoqda",
                    emptyTable: "Ma'lumot yo'q",
                    paginate: { first:"Birinchi", last:"Oxirgi", next:"Keyingi", previous:"Oldingi" }
                }
            });
        });
    </script>
@endpush
