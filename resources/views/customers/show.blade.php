@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-4">
                    <h2>Mijoz Tafsilotlari</h2>

                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>Ism</th>
                            <td>{{ $customer->name }}</td>
                        </tr>
                        <tr>
                            <th>Shartnoma PDF</th>
                            <td>
                                @if($customer->pdf_file)
                                    <a href="{{ asset('storage/' . $customer->pdf_file) }}" target="_blank"
                                       class="btn btn-sm btn-info">
                                        PDF-ni ko‘rish
                                    </a>
                                    <a href="{{ asset('storage/' . $customer->pdf_file) }}" download
                                       class="btn btn-sm btn-success">
                                        Yuklab olish
                                    </a>
                                @else
                                    Fayl yo‘q
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Jami Qarzdorlik (UZS)</th>
                            <td>
                                @php
                                    $balance = $customer->balance;
                                    $balanceClass = $balance < 0 ? 'text-red' : ($balance > 0 ? 'text-green' : 'text-info');
                                    $balanceText = $balance < 0 ? 'Qarzdor' : ($balance > 0 ? 'Ortiqcha' : 'Nol');
                                @endphp
                                <span class="badge {{ $balanceClass }}">
                                    {{ ($balance > 0 ? '+' : '-') . number_format(abs($balance), 0, '.', ' ') }} UZS ({{ $balanceText }})
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>So'ngi ko'rsatkich</th>
                            <td>
                                @if($customer->waterMeter && $customer->waterMeter->readings->count())
                                    {{ $customer->waterMeter->readings->last()->reading }}
                                @else
                                    <em>Ko‘rsatkich mavjud emas</em>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Oila a'zolari soni</th>
                            <td>{{ $customer->family_members }}</td>
                        </tr>
                        <tr>
                            <th>Telefon</th>
                            <td>{{ $customer->phone }}</td>
                        </tr>
                        <tr>
                            <th>Hisoblagich unikal raqami</th>
                            <td>
                                @if($customer->waterMeter)
                                    <a href="{{ route('water_meters.show', $customer->waterMeter->id) }}"
                                       class="badge badge-outline text-blue">
                                        {{ $customer->waterMeter->meter_number }}
                                    </a>
                                @else
                                    Hisoblagich o'rnatilmagan <br>
                                    <a href="{{ route('water_meters.create', ['customer_id' => $customer->id]) }}"
                                       class="btn btn-outline-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="icon icon-tabler icon-tabler-plus" width="24" height="24"
                                             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 5l0 14"/>
                                            <path d="M5 12l14 0"/>
                                        </svg>
                                        Hisoblagich qo'shish
                                    </a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ulangan telegram akkauntlar</th>
                            <td>
                                @foreach($customer->telegramAccounts as $tg)
                                    <a href="https://t.me/{{$tg->username}}" target="_blank">{{$tg->username}}</a>

                                    <!-- O‘chirish tugmasi faqat adminlar uchun -->
                                    <form
                                        action="{{ route('customers.detachTelegram', ['customer' => $customer->id, 'telegram' => $tg->id]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">❌</button>
                                    </form>
                                    <br>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Kompaniya</th>
                            <td>
                                <a href="{{ route('companies.show', $customer->company->id) }}">
                                    {{ $customer->company->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Uy raqami</th>
                            <td>
                                {{ $customer->address }}
                            </td>
                        </tr>
                        <tr>
                            <th>Ko‘cha</th>
                            <td>
                                <a href="{{ route('streets.show', $customer->street->id) }}">
                                    {{ $customer->street->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Mahalla</th>
                            <td>
                                <a href="{{ route('neighborhoods.show', $customer->street->neighborhood->id) }}">
                                    {{ $customer->street->neighborhood->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Shahar</th>
                            <td>
                                <a href="{{ route('cities.show', $customer->street->neighborhood->city->id) }}">
                                    {{ $customer->street->neighborhood->city->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Viloyat</th>
                            <td>
                                <a href="{{ route('regions.show', $customer->street->neighborhood->city->region->id) }}">
                                    {{ $customer->street->neighborhood->city->region->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Hisob Raqami</th>
                            <td>{{ $customer->account_number }}</td>
                        </tr>
                        <tr>
                            <th>Tizimga qo'shgan xodim</th>
                            <td>
                                @if ($customer->createdBy) {{-- Foydalanuvchi obyektini tekshirish --}}
                                <a href="{{ route('users.show', $customer->createdBy->id) }}"> {{-- Obyektdan ID ni olish --}}
                                    {{ $customer->createdBy->name }}
                                </a>
                                @else
                                    Ma'lumot yo'q
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Oxirgi o'zgartirgan xodim</th>
                            <td>
                                @if ($customer->updatedBy) {{-- Foydalanuvchi obyektini tekshirish --}}
                                <a href="{{ route('users.show', $customer->updatedBy->id) }}"> {{-- Obyektdan ID ni olish --}}
                                    {{ $customer->updatedBy->name }}
                                </a>
                                @else
                                    Ma'lumot yo'q
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Faollik</th>
                            <td>
                                @if($customer->is_active)
                                    <span class="badge bg-cyan text-cyan-fg">Faol</span>
                                @else
                                    <span class="badge bg-red text-red-fg">Nofaol</span>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline mt-3">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Ortga</a>
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning">Tahrirlash</a>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Haqiqatan ham o‘chirmoqchimisiz?')">O‘chirish
                        </button>
                    </form>
                    <h1>Hisoblagich so'ngi ko'rsatgichlari:</h1>
                    <ul class="list-group">
                        @foreach($readings as $reading)
                            <li class="list-group-item">
                                <small>Sana: {{ $reading->reading_date }}</small><br>
                                <small>Ko'rsatgich: {{ $reading->reading }}</small><br>
                                <small id="reading-status-{{ $reading->id }}">
                                    @include('customers.partials.reading-status', ['reading' => $reading])
                                </small>
                                <a href="{{ route('meter_readings.show', $reading->id) }}"
                                   class="badge badge-outline text-blue">
                                    Batafsil
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        {{ $readings->appends(['reading_page' => request('reading_page')])->links() }}
                    </div>

                    <h1>Yangi ko'rsatkich qo'shish:</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('meter_readings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if($customer->waterMeter)
                            <input type="hidden" name="water_meter_id" value="{{ $customer->waterMeter->id }}">
                        @else
                            <div class="alert alert-warning">Mijozga hisoblagich o‘rnatilmagan. Ko‘rsatkich qo‘shib
                                bo‘lmaydi.
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="reading">Ko'rsatgich:</label>
                            <input type="number" name="reading" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">O‘qish sanasi:</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="icon icon-1"><path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                            d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path
                                            d="M12 15v3"/></svg>
                                </span>
                                <input name="reading_date" class="form-control" placeholder="Sanani tanlang" required
                                       value="{{ old('reading_date', now()->format('Y-m-d')) }}"
                                       id="datepicker-icon-prepend"/>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Rasm yuklash</label>
                            <input type="file" name="photo" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="confirmed">Tasdiqlanganmi?</label>
                            <select name="confirmed" class="form-control">
                                <option value="1">Ha</option>
                                <option value="0">Yo‘q</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>

                </div>

                <div class="col-md-4">
                    <h3>Invoyslar tarixi</h3>
                    <ul class="list-group">
                        @foreach($invoices as $invoice)
                            <li class="list-group-item">
                                <strong>Invoys #{{ $invoice->invoice_number }}</strong><br>
                                <small>Qaysi oy uchun?: {{ $invoice->billing_period }}</small><br>
                                <small>Holat:
                                    @if($invoice->status == 'pending')
                                        <span class="badge bg-yellow text-yellow-fg">To'liq to‘lanmagan</span>
                                    @elseif($invoice->status == 'paid')
                                        <span class="badge bg-green text-green-fg">To‘langan</span>
                                    @elseif($invoice->status == 'overdue')
                                        <span class="badge bg-red text-red-fg">Muddati o‘tgan</span>
                                    @endif
                                </small><br>
                                <small>Summa: {{ number_format($invoice->amount_due, 0, '.', ' ') }} UZS</small>
                                <a href="{{ route('invoices.show', $invoice->id) }}"
                                   class="badge badge-outline text-blue">
                                    Batafsil
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        {{ $invoices->appends(['payment_page' => request('payment_page')])->links() }}
                    </div>

                    {{-- ------------- YANGI INVOYS QO'SHISH FORMASI ------------- --}}
                    <h3 class="mt-4">Yangi Invoys Qo'shish</h3>
                    <form action="{{ route('invoices.store') }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        {{-- Agar invoys qo'shilgandan keyin shu sahifaga qaytish kerak bo'lsa --}}
                        <input type="hidden" name="redirect_to_customer_show" value="1">

                        <div class="mb-3">
                            <label for="invoice_tariff_id" class="form-label required">Tarif:</label>
                            <select name="tariff_id" id="invoice_tariff_id"
                                    class="form-control @error('tariff_id', 'invoice_form') is-invalid @enderror"
                                    required>
                                @if($activeTariffs->count() > 0)
                                    @foreach($activeTariffs as $tariff)
                                        <option
                                            value="{{ $tariff->id }}" {{ old('tariff_id') == $tariff->id ? 'selected' : '' }}>
                                            {{ $tariff->name ?? "ID: " . $tariff->id }}
                                            ({{ number_format($tariff->price_per_m3, 0, '', ' ') }} so'm/m³
                                            @if($tariff->for_one_person)
                                                , {{ number_format($tariff->for_one_person, 0, '', ' ') }}
                                                so'm/kishi @endif)
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Mijoz kompaniyasi uchun aktiv tariflar mavjud emas.
                                    </option>
                                @endif
                            </select>
                            @error('tariff_id', 'invoice_form') {{-- Xatoliklarni alohida nomlash --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_billing_period_picker" class="form-label required">Hisob davri
                                (YYYY-MM):</label>
                            <div class="input-icon"> {{-- Input-icon qobig'i --}}
                                <span class="input-icon-addon"> {{-- Ikonka uchun joy --}}
                                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                          fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round" class="icon icon-tabler icon-tabler-calendar-month"><path
                                             stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                             d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                             d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M7 14h.01"/><path
                                             d="M10 14h.01"/><path d="M13 14h.01"/><path d="M7 17h.01"/><path
                                             d="M10 17h.01"/><path d="M13 17h.01"/></svg>
                                </span>
                                <input type="text" {{-- type="month" dan "text" ga o'zgartirildi --}}
                                name="billing_period"
                                       id="invoice_billing_period_picker" {{-- Yangi ID --}}
                                       class="form-control @error('billing_period', 'invoice_form') is-invalid @enderror"
                                       placeholder="YYYY-MM formatida"
                                       value="{{ old('billing_period', now()->format('Y-m')) }}"
                                       required
                                       autocomplete="off">
                            </div>
                            @error('billing_period', 'invoice_form')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_amount_due" class="form-label required">Summa (UZS):</label>
                            <input type="number" name="amount_due" id="invoice_amount_due"
                                   class="form-control @error('amount_due', 'invoice_form') is-invalid @enderror"
                                   value="{{ old('amount_due') }}" required min="0" step="any">
                            @error('amount_due', 'invoice_form')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_issue_date_picker" class="form-label required">Hisob varaqa
                                sanasi:</label>
                            <div class="input-icon"> {{-- Input-icon qobig'i --}}
                                <span class="input-icon-addon"> {{-- Ikonka uchun joy --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" class="icon icon-tabler icon-tabler-calendar"><path
                                            stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path
                                            d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M11 15h1"/><path
                                            d="M12 15v3"/></svg>
                                </span>
                                <input type="text" {{-- type="date" dan "text" ga o'zgartirildi --}}
                                name="due_date" {{-- Bazadagi due_date ustuniga yozish uchun name o'zgarmaydi --}}
                                       id="invoice_issue_date_picker" {{-- Yangi ID --}}
                                       class="form-control @error('due_date', 'invoice_form') is-invalid @enderror"
                                       placeholder="Sanani tanlang"
                                       value="{{ old('due_date', now()->format('Y-m-d')) }}"
                                       {{-- Standart qiymat bugungi sana --}}
                                       required
                                       autocomplete="off"> {{-- Brauzer avtomatik to'ldirishini o'chirish --}}
                            </div>
                            @error('due_date', 'invoice_form')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="invoice_status" class="form-label required">Holati:</label>
                            <select name="status" id="invoice_status"
                                    class="form-control @error('status', 'invoice_form') is-invalid @enderror" required>
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                                    To'lanmagan
                                </option>
                                <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>To'langan</option>
                                <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>Muddati
                                    o'tgan
                                </option>
                            </select>
                            @error('status', 'invoice_form')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">Invoys Qo'shish</button>
                    </form>
                    {{-- ------------- YANGI INVOYS QO'SHISH FORMASI TUGADI ------------- --}}
                </div>

                <div class="col-md-4">
                    <h3>To‘lovlar Tarixi</h3>
                    <ul class="list-group">
                        @foreach($payments as $payment)
                            <li class="list-group-item">
                                <strong>To‘lov: {{ number_format($payment->amount, 0, '.', ' ') }} UZS</strong><br>
                                <small>
                                    Usul:
                                    @switch($payment->payment_method)
                                        @case('cash')
                                        Naqd pul
                                        @break
                                        @case('card')
                                        Plastik orqali
                                        @break
                                        @case('transfer')
                                        Bank orqali
                                        @break
                                        @default
                                        Noaniq
                                    @endswitch
                                </small><br>
                                <small>Sana: {{ $payment->created_at }}</small><br>
                                <small>Status:
                                    @switch($payment->status)
                                        @case('completed')
                                        To'langan
                                        @break
                                        @case('failed')
                                        Xatolik
                                        @break
                                        @case('pending')
                                        To'lanmoqda
                                        @break
                                        @default
                                        Noaniq
                                    @endswitch
                                </small>
                                <a href="{{ route('payments.show', $payment->id) }}"
                                   class="badge badge-outline text-blue">
                                    Batafsil
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        {{ $payments->appends(['invoice_page' => request('invoice_page')])->links() }}

                        <h3>To‘lov qabul qilish</h3>
                        <form action="{{ route('payments.store') }}" method="POST" class="mb-3">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                            <input type="hidden" name="redirect_back" value="1">

                            <div class="mb-3">
                                <label for="amount">To‘lov summasi:</label>
                                <input type="number" name="amount" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="payment_method">To‘lov usuli:</label>
                                <select name="payment_method" class="form-control">
                                    <option value="cash">Naqd</option>
                                    <option value="card">Karta</option>
                                    <option value="transfer">Bank o'tkazmasi</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success">To‘lovni kiritish</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                if (window.Litepicker) { // Litepicker obyekti mavjudligini tekshirish
                    if (document.getElementById('invoice_issue_date_picker')) {
                        new Litepicker({
                            element: document.getElementById('invoice_issue_date_picker'),
                            format: 'YYYY-MM-DD',
                            autoApply: true,
                            // showOnFocus: true, // Litepicker odatda text input uchun buni avtomatik qiladi
                            buttonText: {
                                previousMonth: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>',
                                nextMonth: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>',
                            }
                        });
                    }

                    if (document.getElementById('invoice_billing_period_picker')) {
                        new Litepicker({
                            element: document.getElementById('invoice_billing_period_picker'),
                            format: 'YYYY-MM', // Faqat yil va oyni saqlash uchun format
                            autoApply: true,
                            singleMode: true, // Bitta sana tanlash (bu standart, lekin aniqlik uchun)
                            showTooltip: false, // Maslahatlarni o'chirish (ixtiyoriy)

                            // Oylar va Yillarni tanlash uchun ochiladigan menyularni yoqish
                            dropdowns: {
                                months: true, // Oylar uchun ochiladigan menyu
                                years: true,  // Yillar uchun ochiladigan menyu (standart yoki 'asc'/'desc' tartibida)
                                // minYear: 2000, // Minimal yil (ixtiyoriy)
                                // maxYear: null,  // Maksimal yil (ixtiyoriy)
                            },

                            buttonText: {
                                previousMonth: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>',
                                nextMonth: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>',
                            }
                        });
                    }
                } else {
                    console.error('Litepicker is not loaded.'); // Litepicker yuklanmagan bo'lsa xabar
                }
            });
        </script>


@endsection
