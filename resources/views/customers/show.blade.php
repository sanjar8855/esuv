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
                                <small>Ko'rsatgich: {{ $reading->reading }}</small>
                                <small id="reading-status-{{ $reading->id }}">
                                    @include('customers.partials.reading-status', ['reading' => $reading])
                                </small> <br>
                                <a href="{{ route('meter_readings.show', $reading->id) }}"
                                   class="btn btn-icon btn-info" title="Batafsil">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                        <path d="M21 12c-2.4 4 -5.4 6 -9 6s-6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6s6.6 2 9 6" />
                                    </svg>
                                </a>
                                <a href="{{ route('meter_readings.edit', $reading->id) }}"
                                   class="btn btn-icon btn-warning" title="O'zgartirish">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil" width="16" height="16"
                                         viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M4 20h4l10.5 -10.5a1.5 1.5 0 0 0 -4 -4l-10.5 10.5v4"/>
                                        <line x1="13.5" y1="6.5" x2="17.5" y2="10.5"/>
                                    </svg>
                                </a>
                                <form action="{{ route('meter_readings.destroy', $reading->id) }}" method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('Haqiqatan ham bu ko\'rsatkichni o\'chirmoqchimisiz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-danger" title="O'chirish">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round" class="icon">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <line x1="4" y1="7" x2="20" y2="7"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                        </svg>
                                    </button>
                                </form>
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

                        <button type="submit" id="saveBtn" class="btn btn-primary">Saqlash</button>
                    </form>
                    {{-- ------------- YANGI INVOYS QO'SHISH FORMASI TUGADI ------------- --}}
                </div>

                <div class="col-md-6 col-lg-4">
                    {{-- ✅ TO'LOVLAR TARIXI JADVALI --}}
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
                                                    <span class="text-muted">{{ $payment->created_at ? $payment->created_at->format('d.m.Y H:i') : 'Ma\'lumot yo\'q' }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($payment->amount, 0, '.', ' ') }} UZS</strong>
                                                </td>
                                                <td>
                                                    {{ $payment->payment_method_name }}
                                                </td>
                                                {{-- ❌ "Holat" ustuni OLIB TASHLANDI --}}
                                                @if($isCompanyOwner)
                                                    <td>
                                        <span class="text-muted">
                                            {{ optional($payment->createdBy)->name ?? 'Noma\'lum' }}
                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($payment->confirmed)
                                                            {{-- ✅ TASDIQLANGAN --}}
                                                            <span class="text-success">
                                                {{ optional($payment->confirmedBy)->name ?? 'Admin' }}<br>
                                                <small class="text-muted">{{ $payment->confirmed_at ? $payment->confirmed_at->format('d.m.Y H:i') : 'Ma\'lumot yo\'q' }}</small>
                                            </span>
                                                        @else
                                                            {{-- ✅ TASDIQLANMAGAN --}}
                                                            <span class="text-warning">Tasdiqlanmagan</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!$payment->confirmed)
                                                            {{-- ✅ TASDIQLASH TUGMASI --}}
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

                    {{-- ✅ TO'LOV QABUL QILISH FORMASI (ROL ASOSIDA) --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">To'lov qabul qilish</h3>
                        </div>
                        <div class="card-body">
                            @if(session('payment_success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('payment_success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('payments.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                <input type="hidden" name="redirect_back" value="1">

                                <div class="mb-3">
                                    <label for="amount" class="form-label required">To'lov summasi:</label>
                                    <input type="number"
                                           name="amount"
                                           id="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}"
                                           placeholder="Masalan: 50000"
                                           required>
                                    @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-hint">
                                        Joriy qarzdorlik:
                                        <strong class="text-{{ $customer->balance < 0 ? 'danger' : 'success' }}">
                                            {{ number_format(abs($customer->balance), 0, '.', ' ') }} UZS
                                        </strong>
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label required">To'lov usuli:</label>
                                    <select name="payment_method"
                                            id="payment_method"
                                            class="form-select @error('payment_method') is-invalid @enderror"
                                            required>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 Naqd pul</option>
                                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>💳 Plastik karta</option>
                                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>🏦 Bank o'tkazmasi</option>
                                    </select>
                                    @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ✅ FAQAT DIREKTOR UCHUN: Tasdiqlanganmi? --}}
                                @if($isCompanyOwner)
                                    <div class="mb-3">
                                        <label class="form-label">Tasdiqlanganmi?</label>
                                        <div class="form-selectgroup">
                                            <label class="form-selectgroup-item">
                                                <input type="radio"
                                                       name="confirmed"
                                                       value="1"
                                                       class="form-selectgroup-input"
                                                        {{ old('confirmed', '1') == '1' ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success me-1">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Ha, tasdiqlangan
                                </span>
                                            </label>
                                            <label class="form-selectgroup-item">
                                                <input type="radio"
                                                       name="confirmed"
                                                       value="0"
                                                       class="form-selectgroup-input"
                                                        {{ old('confirmed') == '0' ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning me-1">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    Yo'q, keyinroq
                                </span>
                                            </label>
                                        </div>
                                        <small class="form-hint text-muted">
                                            💡 "Ha" tanlasangiz, to'lov darhol tasdiqlangan bo'ladi.
                                            "Yo'q" tanlasangiz, keyinroq tasdiqlanishi kerak.
                                        </small>
                                    </div>
                                @else
                                    {{-- ✅ ODDIY ISHCHI UCHUN: Hidden input --}}
                                    <input type="hidden" name="confirmed" value="0">
                                    <div class="alert alert-info mb-3" role="alert">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="16" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                        </svg>
                                        Sizning to'lovlaringiz direktor tomonidan tasdiqlanadi.
                                    </div>
                                @endif

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        To'lovni kiritish
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                            <polyline points="1 4 1 10 7 10"></polyline>
                                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                        </svg>
                                        Tozalash
                                    </button>
                                </div>
                            </form>
                        </div>
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
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            var btn = document.getElementById('saveBtn');
            if(btn) {
                btn.disabled = true; // Tugmani o'chirib qo'yamiz
                btn.innerHTML = 'Saqlanmoqda...'; // Matnni o'zgartiramiz
            }
        });
    </script>
@endpush

