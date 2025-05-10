@extends('layouts.app')

{{-- TomSelect CSS --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
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
                    <h2>Mijozlar <span class="text-muted">({{ $customersCount }} ta)</span></h2>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary mb-3">Yangi mijoz qo‘shish</a>

                    {{-- Filtrlarni alohida card'ga olish mumkin (ixtiyoriy) --}}
                    <div class="card card-body mb-3">
                        <div class="row g-3">
                            {{-- Qidiruv Formasi --}}
                            <div class="col-md-4">
                                <form method="GET" id="customerSearchForm" class="filter-form">
                                    <label class="form-label">Qidiruv:</label>
                                    <div class="input-group">
                                        <input type="text" id="customerSearchInput" name="search_text"
                                               value="{{ request('search') }}"
                                               placeholder="Ism, telefon, hisob raqam..." class="form-control">
                                        {{-- Qidiruv tugmasini olib tashlasa ham bo'ladi, harflar yozilganda avtomatik qidiradi --}}
                                        {{-- <button type="submit" class="btn btn-secondary">Qidirish</button> --}}
                                    </div>
                                </form>
                            </div>

                            {{-- Ko'cha Bo'yicha Filtr --}}
                            <div class="col-md-4">
                                <form method="GET" id="streetFilterForm" class="filter-form">
                                    <label for="StreetSelect" class="form-label">Ko‘cha:</label>
                                    <select name="street_id" id="StreetSelect"
                                            class="form-select"> {{-- form-control o'rniga form-select --}}
                                        <option value="">Barcha ko‘chalar</option>
                                        @foreach($streets as $street)
                                            <option
                                                value="{{ $street->id }}" {{ request('street_id') == $street->id ? 'selected' : '' }}>
                                                {{ $street->name }} ko'cha,
                                                {{ $street->neighborhood->name }} MFY,
                                                {{ $street->neighborhood->city->name }} shahar/tuman,
                                                {{ $street->neighborhood->city->region->name }} vil.
                                            </option>
                                        @endforeach
                                    </select>
                                    {{-- <button type="submit" class="btn btn-secondary mt-2">Filtrlash</button> --}}
                                </form>
                            </div>

                            {{-- Qarzdorlik Bo'yicha Filtr --}}
                            <div class="col-md-4">
                                <form method="GET" id="debtFilterForm" class="filter-form">
                                    <label for="debtFilterSelect" class="form-label">Qarzdorlik:</label>
                                    <select name="debt" id="debtFilterSelect"
                                            class="form-select"> {{-- form-control o'rniga form-select --}}
                                        <option value="">Barcha mijozlar</option>
                                        <option value="has_debt" {{ request('debt') == 'has_debt' ? 'selected' : '' }}>
                                            Faqat qarzdorlar
                                        </option>
                                    </select>
                                    {{-- <button type="submit" class="btn btn-secondary mt-2">Filtrlash</button> --}}
                                </form>
                            </div>
                        </div>
                    </div>


                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table id="customersTable"
                                   class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    @if(auth()->user()->hasRole('admin'))
                                        <th>Kompaniya</th>
                                    @endif
                                    <th>Ko‘cha</th>
                                    <th>Uy raqami</th>
                                    <th>Ism va Status</th> {{-- Ustun nomini o'zgartirdim --}}
                                    <th>Telefon</th>
                                    <th>Hisoblagich</th>
                                    <th>Balans (UZS)</th> {{-- Ustun nomini o'zgartirdim --}}
                                    <th>Oxirgi ko'rsatkich</th>
                                    <th>Oila a'zolari</th> {{-- Ustun nomini o'zgartirdim --}}
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{-- Ma'lumotlar DataTables tomonidan AJAX orqali yuklanadi --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Laravel sahifalash o'chirildi --}}
                    {{-- <div class="mt-3">
                        {{ $customers->appends(request()->query())->links() }}
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- TomSelect JS --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // TomSelect Init
            var tomSelect = new TomSelect("#StreetSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Ko'chani tanlang yoki yozing...",
                allowEmptyOption: true
                // Agar kerak bo'lsa, qidiruvni yoqish
                // openOnFocus: true,
                // valueField: 'id',
                // labelField: 'name',
                // searchField: ['name'],
            });

            // DataTable Init
            var customersTable = $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('customers.index') }}",
                    type: "GET",
                    data: function (d) {
                        // Tashqi filtr qiymatlarini DataTables so'roviga qo'shish
                        d.search_text = $('#customerSearchInput').val(); // Qidiruv maydoni
                        d.street_id = $('#StreetSelect').val();       // Ko'cha tanlovi
                        d.debt = $('#debtFilterSelect').val();       // Qarzdorlik tanlovi
                    },
                    // Xatoliklarni ushlash (ixtiyoriy)
                    error: function (xhr, error, thrown) {
                        console.error("DataTables AJAX xatosi:", error, thrown);
                        // Foydalanuvchiga xabar berish mumkin
                        alert('Jadval ma\'lumotlarini yuklashda xatolik yuz berdi. Sahifani yangilang yoki administratorga murojaat qiling.');
                    }
                },
                columns: [
                    // Controller'dagi addColumn/editColumn kalitlariga mos kelishi kerak
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                        @if(auth()->user()->hasRole('admin'))
                    {
                        data: 'company_name', name: 'company.name'
                    }, // Kompaniya nomi (name='company.name' saralash/qidirish uchun)
                        @endif
                    {
                        data: 'street_name', name: 'street.name'
                    }, // Ko'cha nomi
                    {data: 'address', name: 'address'},
                    {data: 'name_status', name: 'name'}, // Saralash/qidirish 'name' bo'yicha
                    {data: 'phone', name: 'phone'},
                    {data: 'meter_link', name: 'waterMeter.meter_number', orderable: false, searchable: false}, // Hisoblagich linki
                    {data: 'balance_formatted', name: 'balance', searchable: false}, // Balans (saralash 'balance' bo'yicha)
                    {data: 'last_reading', name: 'last_reading', orderable: false, searchable: false}, // Oxirgi ko'rsatkich (serverda alohida tayyorlanadi)
                    {data: 'family_members', name: 'family_members'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false} // Amallar
                ],
                pageLength: 25,
                // Boshlang'ich saralash (masalan, ID yoki Ism bo'yicha)
                // order: [[{{ auth()->user()->hasRole('admin') ? 1 : 0 }}, 'asc']], // Birinchi ko'rinadigan ustun (N dan keyingi)
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
                // Sekin yozganda qidiruvni jo'natish (debounce) - ixtiyoriy
                searchDelay: 500 // 500ms kutib turadi
            });

            // --- Tashqi filtrlar o'zgarganda DataTables'ni yangilash ---

            // Qidiruv maydoni (harf terilganda yangilash)
            var searchTimeout;
            $('#customerSearchInput').on('keyup', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    customersTable.ajax.reload(); // ajax.reload() jadvalni yangilaydi
                }, 500); // 500ms kutib turib keyin jo'natadi (serverga yuklamani kamaytirish uchun)
            });
            // Qidiruv formasi submit bo'lishini oldini olish (agar Enter bosilsa)
            $('#customerSearchForm').on('submit', function (e) {
                e.preventDefault();
            });

            // Ko'cha tanlanganda yangilash
            $('#StreetSelect').on('change', function () {
                customersTable.ajax.reload();
            });

            // Qarzdorlik tanlanganda yangilash
            $('#debtFilterSelect').on('change', function () {
                customersTable.ajax.reload();
            });

            // Filtr formalarining default submit bo'lishini to'xtatish (agar tugmalari bo'lsa)
            $('#streetFilterForm, #debtFilterForm').on('submit', function (e) {
                e.preventDefault();
            });

        });
    </script>
@endpush
