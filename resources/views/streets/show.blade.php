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
            let addressColumnIndex = isAdmin ? 2 : 1;

            let columns = [
                { data: 'id', name: 'id' },
                // Admin uchun Kompaniya ustuni (shartli)
                // { data: 'company', name: 'company.name' }, // Controllerda formatlanadi
                { data: 'address', name: 'address' },
                { data: 'name', name: 'name' }, // Controllerda formatlanadi
                { data: 'phone', name: 'phone' },
                { data: 'meter', name: 'waterMeter.meter_number', orderable: false, searchable: false }, // Controllerda formatlanadi
                { data: 'balance', name: 'balance', searchable: false }, // Controllerda formatlanadi, 'balance' accessorini taxmin qilamiz
                { data: 'last_reading', name: 'last_reading', orderable: false, searchable: false }, // Controllerda formatlanadi
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
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
                },
                columnDefs: [ // <-- YANGI QISM
                    { type: 'natural', targets: addressColumnIndex } // "Uy raqami" ustuniga tabiiy saralashni qo'llash
                ]
            });
        });

        function naturalSort (a, b) {
            var re = /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi,
                sre = /(^[ ]*|[ ]*$)/g,
                dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
                hre = /^0x[0-9a-f]+$/i,
                ore = /^0/,
                i = function(s) { return naturalSort.insensitive && (''+s).toLowerCase() || ''+s; },
                // convert all to strings strip whitespace
                x = i(a).replace(sre, '') || '',
                y = i(b).replace(sre, '') || '',
                // chunk/tokenize
                xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
                yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
                // numeric, hex or date detection
                xD = parseInt(x.match(hre), 16) || (xN.length !== 1 && x.match(dre) && Date.parse(x)),
                yD = parseInt(y.match(hre), 16) || xD && y.match(dre) && Date.parse(y) || null,
                oFxNcL, oFyNcL;
            // first try and sort Hex codes or Dates
            if (yD) {
                if ( xD < yD ) { return -1; }
                else if ( xD > yD ) { return 1; }
            }
            // natural sorting through split numeric strings and default strings
            for(var cLoc=0, numS=Math.max(xN.length, yN.length); cLoc < numS; cLoc++) {
                oFxNcL = !(xN[cLoc] || '').match(ore) && parseFloat(xN[cLoc]) || xN[cLoc] || 0;
                oFyNcL = !(yN[cLoc] || '').match(ore) && parseFloat(yN[cLoc]) || yN[cLoc] || 0;
                if (isNaN(oFxNcL) !== isNaN(oFyNcL)) { return (isNaN(oFxNcL)) ? 1 : -1; }
                else if (typeof oFxNcL !== typeof oFyNcL) {
                    oFxNcL += '';
                    oFyNcL += '';
                }
                if (oFxNcL < oFyNcL) { return -1; }
                if (oFxNcL > oFyNcL) { return 1; }
            }
            return 0;
        }

        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "natural-asc": function (a, b) {
                return naturalSort(a, b);
            },
            "natural-desc": function (a, b) {
                return naturalSort(b, a);
            }
        });

    </script>
@endpush
