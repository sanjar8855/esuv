@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-6 col-lg-4">
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
                            <th>Hisob Raqami</th>
                            <td>{{ $customer->account_number }}</td>
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
                            <th>Ulangan telegram akkauntlar</th>
                            <td>
                                @foreach($customer->telegramAccounts as $tg)
                                    <a href="https://t.me/{{$tg->username}}" target="_blank">{{$tg->username}}</a>

                                    <!-- O‘chirish tugmasi faqat adminlar uchun -->
                                    <form action="{{ route('customers.detachTelegram', [$customer->id, $tg->id]) }}"
                                          method="POST" style="display:inline;"
                                          onsubmit="return confirm('Telegram akkauntni uzmoqchimisiz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                title="Telegram akkauntni uzish">
                                            ❌
                                        </button>
                                    </form>
                                    <br>
                                @endforeach
                            </td>
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
                            <th>So'ngi ko'rsatkich</th>
                            <td>
                                @if($customer->waterMeter)
                                    @php
                                        // ✅ Controller eager loading qilgan
                                        $lastReading = $customer->waterMeter->readings->first();
                                    @endphp

                                    @if($lastReading)
                                        {{ $lastReading->reading }}
                                        <br>
                                        <small class="text-muted">
                                            ({{ $lastReading->reading_date }})
                                        </small>
                                    @else
                                        <em>Ko'rsatkich mavjud emas</em>
                                    @endif
                                @else
                                    <em>Hisoblagich o'rnatilmagan</em>
                                @endif
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
                            <th>Tizimga qo'shgan xodim</th>
                            <td>
                                @if ($customer->createdBy)
                                    {{-- Foydalanuvchi obyektini tekshirish --}}
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
                                @if ($customer->updatedBy)
                                    {{-- Foydalanuvchi obyektini tekshirish --}}
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
                </div>

                <div class="col-md-6 col-lg-4">
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
                        {{ $readings->appends(request()->except('reading_page'))->links() }}
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
                    {{-- ------------- YANGI INVOYS QO'SHISH FORMASI TUGADI ------------- --}}
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                To'lovlar tarixi
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($customer->payments->isEmpty())
                                <div class="empty">
                                    <p class="empty-title">To'lovlar mavjud emas</p>
                                    <p class="empty-subtitle text-muted">Hozircha hech qanday to'lov qilinmagan.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    {{-- ✅ JADVALNI IXCHAM QILISH --}}
                                    <table class="table table-sm table-vcenter card-table">
                                        <thead>
                                        <tr>
                                            {{-- ✅ Ustun kengliklarini optimallashtirish --}}
                                            <th style="width: 15%;">Sana va Vaqt</th>
                                            <th style="width: 18%;">Summa</th>
                                            <th style="width: 15%;">Usul</th>
                                            {{-- ❌ "Holat" ustuni OLIB TASHLANDI --}}
                                            @if($isCompanyOwner)
                                                <th style="width: 18%;">Kim yaratgan</th>
                                                <th style="width: 24%;">Tasdiqlangan</th>
                                                <th style="width: 10%;">Amallar</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($customer->payments as $payment)
                                            {{-- ✅ Tasdiqlanmagan to'lovlarni yorqin ko'rsatish --}}
                                            <tr class="{{ !$payment->confirmed ? 'bg-yellow-lt' : '' }}">
                                                <td>
                                                    {{-- ✅ TO'LIQ SANA VA VAQT --}}
                                                    <span class="text-muted">{{ $payment->created_at->format('d.m.Y H:i') }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($payment->amount, 0, '.', ' ') }} UZS</strong>
                                                </td>
                                                <td>
                                                    {{ $payment->payment_method_name }}
                                                </td>
                                                {{-- ❌ "Holat" ustuni BUTUNLAY OLIB TASHLANDI --}}
                                                @if($isCompanyOwner)
                                                    <td>
                                                        {{-- ✅ TO'G'RILANDI: createdBy relationsiga mos keladi --}}
                                                        <span class="text-muted">
                                        {{ optional($payment->createdBy)->name ?? 'Noma\'lum' }}
                                    </span>
                                                    </td>
                                                    <td>
                                                        @if($payment->confirmed)
                                                            {{-- ✅ IKONKASIZ, FAQAT MATN --}}
                                                            <span class="text-success">
                                            {{ optional($payment->confirmedBy)->name ?? 'Admin' }}<br>
                                            <small class="text-muted">{{ $payment->confirmed_at->format('d.m.Y H:i') }}</small>
                                        </span>
                                                        @else
                                                            {{-- ✅ IKONKASIZ, FAQAT MATN --}}
                                                            <span class="text-warning">Tasdiqlanmagan</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!$payment->confirmed)
                                                            {{-- ✅ SVG IKONKA OLIB TASHLANDI --}}
                                                            <form action="{{ route('payments.confirm', $payment) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-sm btn-success"
                                                                        onclick="return confirm('To\'lovni tasdiqlaysizmi?')">
                                                                    Tasdiqlash
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ✅ PAGINATION --}}
                    <div class="mt-3">
                        {{ $payments->appends(request()->except('payment_page'))->links() }}
                    </div>

                    {{-- ✅ TO'LOV QABUL QILISH FORMASI --}}
                    <h3>To'lov qabul qilish</h3>
                    <form action="{{ route('payments.store') }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <input type="hidden" name="redirect_back" value="1">

                        <div class="mb-3">
                            <label for="amount">To'lov summasi:</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method">To'lov usuli:</label>
                            <select name="payment_method" class="form-control">
                                <option value="cash">Naqd</option>
                                <option value="card">Karta</option>
                                <option value="transfer">Bank o'tkazmasi</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">To'lovni kiritish</button>
                    </form>

                    {{--
                    📌 KELAJAKDA O'ZGARISH MUMKIN:
                    1. Agar to'lov sanasida ham vaqt kerak bo'lsa (payment_date):
                       - Migration orqali payment_date ni 'datetime' ga o'zgartirish
                       - Model castingda 'datetime' qilish

                    2. Agar "Holat" ustunini qayta ko'rmoqchi bo'lsa:
                       - <th>Holat</th> qo'shish
                       - Badge'lar bilan ko'rsatish (lekin ikonkasiz)

                    3. Agar bazadagi ustun nomlarini o'zgartirmoqchi bo'lsangiz:
                       - Migration yozib 'created_by_user_id' -> 'created_by' ga rename qilish
                       - Lekin bu xavfli, mavjud ma'lumotlar yo'qolishi mumkin
                    --}}
                    <div class="mt-3">
                        {{ $payments->appends(request()->except('payment_page'))->links() }}

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
    </div>
@endsection
@push('scripts')
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
@endpush

