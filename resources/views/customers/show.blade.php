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
                                    {{ ($balance > 0 ? '+' : '-') . number_format(abs($balance), 2) }} UZS ({{ $balanceText }})
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>So'ngi ko'rsatkich</th>
                            <td>
                                @if($customer->waterMeter && $customer->waterMeter->readings->count())
                                    {{ $customer->waterMeter->readings->first()->reading }}
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
                                    <a href="{{ route('water_meters.show', $customer->waterMeter->id) }}">
                                        {{ $customer->waterMeter->meter_number }}
                                    </a>
                                @else
                                    Hisoblagich o'rnatilmagan
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

                    <h1>Hisoblagich so'ngi ko'rsatgichlari:</h1>
                    <ul class="list-group">
                        @foreach($customer->waterMeter->readings as $reading)
                            <li class="list-group-item">
                                <small>Sana: {{ $reading->reading_date }}</small><br>
                                <small>Ko'rsatgich: {{ $reading->reading }}</small><br>
                                <small>
                                    Holat: {!! $reading->confirmed
                                        ? '<span class="badge bg-green text-green-fg">Tasdiqlangan</span>'
                                        : '<span class="badge bg-red text-red-fg">Tasdiqlanmagan</span>'
                                    !!}</small><br>
                                @if($reading->photo)
                                    <a href="{{ asset('storage/' . $reading->photo) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $reading->photo) }}" alt="Ko'rsatkich rasmi"
                                             width="50">
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>

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

                        <input type="hidden" name="water_meter_id" value="{{ $customer->waterMeter->id }}">

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
                                <small>Oy: {{ $invoice->billing_period }}</small><br>
                                <small>Holat:
                                    @if($invoice->status == 'pending')
                                        <span class="badge bg-yellow text-yellow-fg">To'liq to‘lanmagan</span>
                                    @elseif($invoice->status == 'paid')
                                        <span class="badge bg-green text-green-fg">To‘langan</span>
                                    @elseif($invoice->status == 'overdue')
                                        <span class="badge bg-red text-red-fg">Muddati o‘tgan</span>
                                    @endif
                                </small><br>
                                <small>Summa: {{ number_format($invoice->amount_due, 2) }} UZS</small>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        {{ $invoices->appends(['payment_page' => request('payment_page')])->links() }}
                    </div>

                </div>

                <div class="col-md-4">
                    <h3>To‘lovlar Tarixi</h3>
                    <ul class="list-group">
                        @foreach($payments as $payment)
                            <li class="list-group-item">
                                <strong>To‘lov: {{ number_format($payment->amount, 2) }} UZS</strong><br>
                                <small>Usul: {{ ucfirst($payment->payment_method) }}</small><br>
                                <small>Sana: {{ $payment->payment_date }}</small><br>
                                <small>Status: {{ $payment->status }}</small>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        {{ $payments->appends(['invoice_page' => request('invoice_page')])->links() }}
                    </div>

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

                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Ortga</a>
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning">Tahrirlash</a>
                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Haqiqatan ham o‘chirmoqchimisiz?')">O‘chirish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
