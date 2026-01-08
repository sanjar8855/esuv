@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ommaviy Ko'rsatkich va To'lov Kiritish</h3>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible" role="alert">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="alert-title">Xatoliklar topildi!</h4>
                                            <div class="text-muted">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="alert-title">Muvaffaqiyatli!</h4>
                                            <div class="text-muted">{{ session('success') }}</div>
                                        </div>
                                    </div>
                                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                                </div>
                            @endif

                            {{-- Filtrlar --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="neighborhood_id" class="form-label form-label-sm">MFY ni tanlang</label>
                                    <select id="neighborhood_id" class="form-select form-select-sm">
                                        <option value="">-- MFY ni tanlang --</option>
                                        @foreach($neighborhoods as $neighborhood)
                                            <option value="{{ $neighborhood->id }}"
                                                {{ session('selected_neighborhood') == $neighborhood->id ? 'selected' : '' }}>
                                                {{ $neighborhood->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="street_id" class="form-label form-label-sm">Ko'chani tanlang</label>
                                    <select id="street_id" class="form-select form-select-sm" disabled>
                                        <option value="">-- Avval MFY ni tanlang --</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">&nbsp;</label>
                                    <button type="button" id="loadBtn" class="btn btn-primary btn-sm w-100" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M12 5l0 14"></path>
                                            <path d="M5 12l14 0"></path>
                                        </svg>
                                        Mijozlarni yuklash
                                    </button>
                                </div>
                            </div>

                            {{-- Jadval --}}
                            <form id="massEntryForm" action="{{ route('mass_entry.save') }}" method="POST">
                                @csrf
                                <div id="customersTableContainer" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="20%">FIO</th>
                                                    <th width="10%">Uy</th>
                                                    <th width="12%">Hisob №</th>
                                                    <th width="10%">Songi</th>
                                                    <th width="12%">Sana</th>
                                                    <th width="12%">Yangi</th>
                                                    <th width="12%">To'lov</th>
                                                    <th width="12%">Balans</th>
                                                </tr>
                                            </thead>
                                            <tbody id="customersTableBody">
                                                {{-- AJAX dan yuklangan --}}
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"></path>
                                                <circle cx="12" cy="14" r="2"></circle>
                                                <polyline points="14 4 14 8 8 8 8 4"></polyline>
                                            </svg>
                                            Saqlash
                                        </button>
                                        <button type="button" id="clearBtn" class="btn btn-secondary btn-sm">Tozalash</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
$(document).ready(function() {
    const selectedStreetId = {{ session('selected_street') ?? 'null' }};

    // MFY o'zgarganda
    $('#neighborhood_id').on('change', function() {
        const neighborhoodId = $(this).val();

        $('#street_id').html('<option value="">-- Yuklanmoqda... --</option>').prop('disabled', true);
        $('#loadBtn').prop('disabled', true);
        $('#customersTableContainer').hide();

        if (!neighborhoodId) {
            $('#street_id').html('<option value="">-- Avval MFY ni tanlang --</option>');
            return;
        }

        // AJAX - Ko'chalarni yuklash
        $.ajax({
            url: '{{ route('mass_entry.get_streets') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                neighborhood_id: neighborhoodId
            },
            success: function(streets) {
                let options = '<option value="">-- Ko\'chani tanlang --</option>';
                streets.forEach(function(street) {
                    const selected = (selectedStreetId && street.id == selectedStreetId) ? 'selected' : '';
                    options += `<option value="${street.id}" ${selected}>${street.name}</option>`;
                });
                $('#street_id').html(options).prop('disabled', false);

                // Agar selected street bo'lsa, avtomatik yuklash
                if (selectedStreetId) {
                    $('#loadBtn').prop('disabled', false);
                    $('#loadBtn').click();
                }
            },
            error: function() {
                alert('Ko\'chalarni yuklashda xatolik yuz berdi!');
                $('#street_id').html('<option value="">-- Xatolik --</option>');
            }
        });
    });

    // Ko'cha o'zgarganda
    $('#street_id').on('change', function() {
        const streetId = $(this).val();
        $('#loadBtn').prop('disabled', !streetId);
        $('#customersTableContainer').hide();
    });

    // Mijozlarni yuklash
    $('#loadBtn').on('click', function() {
        const streetId = $('#street_id').val();

        if (!streetId) {
            alert('Ko\'chani tanlang!');
            return;
        }

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Yuklanmoqda...');

        // AJAX - Mijozlarni yuklash
        $.ajax({
            url: '{{ route('mass_entry.load_customers') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                street_id: streetId
            },
            success: function(customers) {
                $('#loadBtn').prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg> Mijozlarni yuklash');

                if (customers.length === 0) {
                    alert('Bu ko\'chada mijozlar topilmadi!');
                    return;
                }

                // Jadvalga qo'shish
                let rows = '';
                customers.forEach(function(customer, index) {
                    const balanceClass = customer.balance < 0 ? 'text-danger' : (customer.balance > 0 ? 'text-success' : 'text-muted');

                    rows += `
                        <tr>
                            <td class="text-nowrap small">${customer.name}</td>
                            <td class="small">${customer.address}</td>
                            <td class="small">${customer.account_number}</td>
                            <td class="small">${customer.last_reading}</td>
                            <td class="small">${customer.last_reading_date}</td>
                            <td>
                                <input type="number"
                                       name="entries[${index}][new_reading]"
                                       class="form-control form-control-sm"
                                       min="${parseFloat(customer.last_reading) + 0.01}"
                                       step="0.01"
                                       style="width: 100px;">
                                <input type="hidden" name="entries[${index}][customer_id]" value="${customer.id}">
                                <input type="hidden" name="entries[${index}][water_meter_id]" value="${customer.water_meter_id}">
                            </td>
                            <td>
                                <input type="number"
                                       name="entries[${index}][payment_amount]"
                                       class="form-control form-control-sm"
                                       min="0"
                                       step="0.01"
                                       style="width: 100px;">
                            </td>
                            <td class="small ${balanceClass}">
                                ${customer.balance.toLocaleString()} so'm
                            </td>
                        </tr>
                    `;
                });

                $('#customersTableBody').html(rows);
                $('#customersTableContainer').show();
            },
            error: function() {
                $('#loadBtn').prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg> Mijozlarni yuklash');
                alert('Mijozlarni yuklashda xatolik yuz berdi!');
            }
        });
    });

    // Tozalash
    $('#clearBtn').on('click', function() {
        if (confirm('Barcha ma\'lumotlarni tozalashni xohlaysizmi?')) {
            $('#customersTableBody').html('');
            $('#customersTableContainer').hide();
        }
    });

    // Agar selected neighborhood bo'lsa, avtomatik ko'chalarni yuklash
    @if(session('selected_neighborhood'))
        $('#neighborhood_id').trigger('change');
    @endif
});
</script>
@endpush
@endsection
