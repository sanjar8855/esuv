@extends('layouts.app')

{{-- DataTables CSS (agar layouts/app.blade.php da umumiy qo'shilmagan bo'lsa) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* DataTables elementlari uchun Bootstrap 5 stillari (kerak bo'lsa) */
    #paymentsTable {
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
        border-radius: var(--tblr-border-radius); /* Tabler uslubi */
    }
</style>

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>To‘lovlar Ro‘yxati (@if(isset($paymentsCount)){{ $paymentsCount }}@else 0 @endif ta)</h1>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary mb-3">Yangi To‘lov Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table id="paymentsTable"
                                   class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>N</th>
                                    <th>Mijoz</th>
                                    <th>Invoys Raqami</th>
                                    <th>Miqdori (UZS)</th>
                                    <th>To‘lov Usuli</th>
                                    <th>To‘lov Sanasi</th>
                                    <th>Yaratilgan Vaqti</th> {{-- Yangi ustun --}}
                                    <th>Holati</th>
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
            $('#paymentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('payments.index') }}", // Ma'lumotlarni shu yerdan oladi
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false}, // N
                    {data: 'customer_link', name: 'customer.name'}, // Mijoz (Controllerda 'customer_name' edi, 'customer_link' ga o'zgartirdim)
                    {data: 'invoice_display', name: 'invoice.invoice_number'}, // Invoys Raqami (Controllerda 'invoice_number' edi)
                    {data: 'amount', name: 'amount'}, // Miqdori
                    {data: 'payment_method_display', name: 'payment_method'}, // To'lov usuli (Controllerda 'payment_method_display' edi)
                    {data: 'payment_date_formatted', name: 'payment_date'}, // To'lov Sanasi
                    {data: 'created_at_formatted', name: 'created_at'}, // Yaratilgan Vaqti
                    {data: 'status_display', name: 'status'}, // Holat (Controllerda 'status_display' edi)
                    {data: 'actions', name: 'actions', orderable: false, searchable: false} // Amallar
                ],
                order: [[6, 'desc']], // Boshlang'ich saralash: "Yaratilgan Vaqti" (6-indeks) bo'yicha kamayish tartibida
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
