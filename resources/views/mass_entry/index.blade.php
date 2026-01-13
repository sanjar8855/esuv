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
                                @if(auth()->user()->hasRole('admin'))
                                <div class="col-md-3">
                                    <label for="company_id" class="form-label form-label-sm">Kompaniyani tanlang</label>
                                    <select id="company_id" class="form-select form-select-sm">
                                        <option value="">-- Kompaniyani tanlang --</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="col-md-{{ auth()->user()->hasRole('admin') ? '3' : '4' }}">
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

                                <div class="col-md-{{ auth()->user()->hasRole('admin') ? '3' : '4' }}">
                                    <label for="street_id" class="form-label form-label-sm">Ko'chani tanlang</label>
                                    <select id="street_id" class="form-select form-select-sm" disabled>
                                        <option value="">-- Avval MFY ni tanlang --</option>
                                    </select>
                                </div>

                                <div class="col-md-{{ auth()->user()->hasRole('admin') ? '3' : '4' }}">
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
document.addEventListener('DOMContentLoaded', function() {
    const selectedStreetId = {{ session('selected_street') ?? 'null' }};
    const neighborhoodSelect = document.getElementById('neighborhood_id');
    const streetSelect = document.getElementById('street_id');
    const loadBtn = document.getElementById('loadBtn');
    const clearBtn = document.getElementById('clearBtn');
    const customersTableBody = document.getElementById('customersTableBody');
    const customersTableContainer = document.getElementById('customersTableContainer');

    // MFY o'zgarganda
    neighborhoodSelect.addEventListener('change', function() {
        const neighborhoodId = this.value;

        streetSelect.innerHTML = '<option value="">-- Yuklanmoqda... --</option>';
        streetSelect.disabled = true;
        loadBtn.disabled = true;
        customersTableContainer.style.display = 'none';

        if (!neighborhoodId) {
            streetSelect.innerHTML = '<option value="">-- Avval MFY ni tanlang --</option>';
            return;
        }

        // Fetch - Ko'chalarni yuklash
        fetch('{{ route('mass_entry.get_streets') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ neighborhood_id: neighborhoodId })
        })
        .then(response => response.json())
        .then(streets => {
            let options = '<option value="">-- Ko\'chani tanlang --</option>';
            streets.forEach(street => {
                const selected = (selectedStreetId && street.id == selectedStreetId) ? 'selected' : '';
                options += `<option value="${street.id}" ${selected}>${street.name}</option>`;
            });
            streetSelect.innerHTML = options;
            streetSelect.disabled = false;

            // Agar selected street bo'lsa, avtomatik yuklash
            if (selectedStreetId) {
                loadBtn.disabled = false;
                loadBtn.click();
            }
        })
        .catch(error => {
            alert('Ko\'chalarni yuklashda xatolik yuz berdi!');
            streetSelect.innerHTML = '<option value="">-- Xatolik --</option>';
        });
    });

    // Ko'cha o'zgarganda
    streetSelect.addEventListener('change', function() {
        const streetId = this.value;
        loadBtn.disabled = !streetId;
        customersTableContainer.style.display = 'none';
    });

    // Mijozlarni yuklash
    loadBtn.addEventListener('click', function() {
        const streetId = streetSelect.value;

        if (!streetId) {
            alert('Ko\'chani tanlang!');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Yuklanmoqda...';

        // Fetch - Mijozlarni yuklash
        fetch('{{ route('mass_entry.load_customers') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ street_id: streetId })
        })
        .then(response => response.json())
        .then(customers => {
            loadBtn.disabled = false;
            loadBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg> Mijozlarni yuklash';

            if (customers.length === 0) {
                alert('Bu ko\'chada mijozlar topilmadi!');
                return;
            }

            // Jadvalga qo'shish
            let rows = '';
            customers.forEach((customer, index) => {
                const balanceClass = customer.balance < 0 ? 'text-danger' : (customer.balance > 0 ? 'text-success' : 'text-muted');

                rows += `
                    <tr>
                        <td class="text-nowrap small">
                            <a href="/customers/${customer.id}" class="text-primary">${customer.name}</a>
                        </td>
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

            customersTableBody.innerHTML = rows;
            customersTableContainer.style.display = 'block';
        })
        .catch(error => {
            loadBtn.disabled = false;
            loadBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg> Mijozlarni yuklash';
            alert('Mijozlarni yuklashda xatolik yuz berdi!');
        });
    });

    // Tozalash
    clearBtn.addEventListener('click', function() {
        if (confirm('Barcha ma\'lumotlarni tozalashni xohlaysizmi?')) {
            customersTableBody.innerHTML = '';
            customersTableContainer.style.display = 'none';
        }
    });

    // Agar selected neighborhood bo'lsa, avtomatik ko'chalarni yuklash
    @if(session('selected_neighborhood'))
        neighborhoodSelect.dispatchEvent(new Event('change'));
    @endif
});
</script>
@endpush
@endsection
