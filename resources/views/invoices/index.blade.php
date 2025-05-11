@extends('layouts.app')

{{-- DataTables CSS (agar layouts/app.blade.php da umumiy qo'shilmagan bo'lsa) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #invoicesTable {
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
                    <h2>Hisob-fakturalar (@if(isset($invoicesCount)){{ $invoicesCount }}@else 0 @endif ta)</h2>
                    <div class="d-flex mb-3">
                        <a href="{{ route('invoices.create') }}" class="btn btn-primary">Yangi hisob-faktura</a>
                        {{-- Oylik Invoyslarni Generatsiya Qilish Tugmasi --}}
                        @can('generate_invoices') {{-- Agar ruxsatnoma bo'lsa (ixtiyoriy) --}}
                        <form action="{{ route('invoices.generate') }}" method="POST" class="ms-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-info">Oylik Invoyslarni Generatsiya Qilish
                            </button>
                        </form>
                        @endcan
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table id="invoicesTable"
                                   class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Mijoz</th>
                                    {{-- <th>Tarif</th> --}}
                                    <th>Hisob raqami</th>
                                    <th>Davr</th>
                                    <th>Summa (UZS)</th>
                                    <th>Holat</th>
                                    <th>Qo'shilgan vaqt</th>
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
            $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('invoices.index') }}", // Ma'lumotlarni shu yerdan oladi
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false}, // N
                    {data: 'customer_link', name: 'customer.name'}, // Mijoz
                    // { data: 'tariff_info', name: 'tariff.name' }, // Agar tarif ustuni kerak bo'lsa
                    {data: 'invoice_number', name: 'invoice_number'}, // Hisob raqami
                    {data: 'billing_period', name: 'billing_period'}, // Davr
                    {data: 'amount_due', name: 'amount_due'}, // Summa
                    {data: 'status_display', name: 'status'}, // Holat
                    {data: 'created_at_formatted', name: 'created_at'}, // Qo'shilgan vaqt
                    {data: 'actions', name: 'actions', orderable: false, searchable: false} // Amallar
                ],
                order: [[6, 'desc']], // Boshlang'ich saralash: "Qo'shilgan vaqt" (6-indeks) bo'yicha kamayish tartibida
                pageLength: 25, // Sahifada 25 ta yozuv
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
                    },
                    "aria": {
                        "sortAscending": ": ustunni o'sish tartibida saralash uchun aktivlashtirish",
                        "sortDescending": ": ustunni kamayish tartibida saralash uchun aktivlashtirish"
                    }
                }
            });
        });
    </script>
@endpush
