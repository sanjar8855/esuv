@extends('layouts.app')

{{-- DataTables CSS (Agar layoutda bo'lmasa) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* DataTables elementlari uchun Bootstrap 5 stillari */
    #customersTable { width: 100% !important; }
    .dataTables_length select.form-select,
    .dataTables_filter input.form-control {
        height: calc(2.25rem + 2px); padding-top: 0.375rem; padding-bottom: 0.375rem;
        padding-left: 0.75rem; font-size: 0.875rem; line-height: 1.5;
    }
    /* Balans uchun ranglar */
    .balance-positive { color: green; }
    .balance-negative { color: red; }
    .balance-zero { color: grey; }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Ko‘cha Tafsilotlari</h1>
                    <p><strong>Mahalla:</strong>
                        <a href="{{ route('neighborhoods.show', $street->neighborhood->id) }}" class="badge badge-outline text-blue">
                            {{ $street->neighborhood->name }}
                        </a>
                    </p>
                    <p><strong>Ko‘cha Nomi:</strong> {{ $street->name }}</p>

                    <a href="{{ route('streets.index') }}" class="btn btn-secondary mb-3">Ortga</a>

                    <h2>{{ $street->name }} ko‘chasidagi mijozlar ({{ $customersCount }} ta)</h2>

                    <div class="card">
                        <div class="table-responsive">
                            {{-- Mijozlar jadvaliga ID beramiz --}}
                            <table id="customersTable" class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th> {{-- N o'rniga ID --}}
                                    @if(auth()->user()->hasRole('admin'))
                                        <th>Kompaniya</th>
                                    @endif
                                    <th>Uy raqami</th>
                                    <th>Ism</th>
                                    <th>Telefon</th>
                                    <th>Hisoblagich</th>
                                    <th>Qarzdorlik</th>
                                    <th>Oxirgi ko'rsatkich</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{-- Ma'lumotlar DataTables tomonidan AJAX orqali yuklanadi --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Laravel pagination olib tashlandi --}}
                    {{-- <div class="mt-3">{{ $customers->links() }}</div> --}}
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
            const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }}; // Adminligini tekshirish

            let columns = [
                { data: 'id', name: 'id' },
                // Admin uchun Kompaniya ustuni (shartli)
                // { data: 'company', name: 'company.name' }, // Controllerda formatlanadi
                { data: 'address', name: 'address' },
                { data: 'name', name: 'name' }, // Controllerda formatlanadi
                { data: 'phone', name: 'phone' },
                { data: 'meter', name: 'waterMeter.meter_number', orderable: false, searchable: false }, // Controllerda formatlanadi
                { data: 'balance', name: 'balance', searchable: false }, // Controllerda formatlanadi, 'balance' accessorini taxmin qilamiz
                { data: 'last_reading', name: 'last_reading', orderable: false, searchable: false } // Controllerda formatlanadi
            ];

            // Agar admin bo'lsa, Kompaniya ustunini boshiga qo'shamiz (ID dan keyin)
            if (isAdmin) {
                columns.splice(1, 0, { data: 'company', name: 'company.name' }); // 1-indeksga qo'shish
            }

            $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('streets.show', $street->id) }}", // Joriy sahifa route'idan ma'lumot olamiz
                columns: columns, // Dinamik `columns` massivini ishlatamiz
                order: [[isAdmin ? 2 : 1, 'asc']], // Boshlang'ich saralash (Uy raqami bo'yicha) - admin bo'lsa indeks o'zgaradi
                pageLength: 50,
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
