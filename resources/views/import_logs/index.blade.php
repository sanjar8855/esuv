@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Import Loglari ({{ number_format($importLogsCount) }} ta)</h3>
                        </div>
                        <div class="card-body">
                            {{-- Filtrlar --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="filter_import_type" class="form-label">Import Turi</label>
                                    <select id="filter_import_type" class="form-select">
                                        <option value="">Barchasi</option>
                                        <option value="customers">Mijozlar</option>
                                        <option value="meter_readings">Ko'rsatkichlar</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filter_status" class="form-label">Holat</label>
                                    <select id="filter_status" class="form-select">
                                        <option value="">Barchasi</option>
                                        <option value="processing">Jarayonda</option>
                                        <option value="completed">Tugallandi</option>
                                        <option value="completed_with_errors">Xatoliklar bilan</option>
                                        <option value="failed">Muvaffaqiyatsiz</option>
                                    </select>
                                </div>
                                @if(auth()->user()->hasRole('admin'))
                                    <div class="col-md-4">
                                        <label for="filter_company" class="form-label">Kompaniya</label>
                                        <select id="filter_company" class="form-select">
                                            <option value="">Barchasi</option>
                                            {{-- Bu yerda kompaniyalar ro'yxati kerak bo'ladi --}}
                                        </select>
                                    </div>
                                @endif
                            </div>

                            {{-- DataTable --}}
                            <div class="table-responsive">
                                <table id="import-logs-table" class="table table-vcenter card-table table-striped">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Turi</th>
                                        <th>Fayl Nomi</th>
                                        <th>Foydalanuvchi</th>
                                        <th>Kompaniya</th>
                                        <th>Xulosa</th>
                                        <th>Holat</th>
                                        <th>Sana</th>
                                        <th>Amallar</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let table = $('#import-logs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('import_logs.index') }}",
                    data: function (d) {
                        d.import_type = $('#filter_import_type').val();
                        d.status = $('#filter_status').val();
                        @if(auth()->user()->hasRole('admin'))
                        d.company_id = $('#filter_company').val();
                        @endif
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'import_type_display', name: 'import_type'},
                    {data: 'file_name', name: 'file_name'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'company_name', name: 'company_name'},
                    {data: 'summary', name: 'summary', orderable: false, searchable: false},
                    {data: 'status_badge', name: 'status'},
                    {data: 'created_at_formatted', name: 'created_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                order: [[0, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/uz.json'
                }
            });

            // Filtr o'zgarganda jadvalni yangilash
            $('#filter_import_type, #filter_status, #filter_company').on('change', function () {
                table.draw();
            });
        });
    </script>
@endpush
