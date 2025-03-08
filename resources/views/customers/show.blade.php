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
                                    <a href="{{ asset('storage/' . $customer->pdf_file) }}" target="_blank" class="btn btn-sm btn-info">
                                        PDF-ni ko‘rish
                                    </a>
                                    <a href="{{ asset('storage/' . $customer->pdf_file) }}" download class="btn btn-sm btn-success">
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
                                    <a href="https://t.me/{{$tg->username}}" target="_blank">{{$tg->username}}</a>,
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
                            <th>Manzil</th>
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

                <div class="col-md-4">
                    <h3>Mijozning Invoice va To‘lovlari</h3>
                    <ul class="list-group">
                        @foreach($invoices as $invoice)
                            <li class="list-group-item">
                                <strong>Invoice #{{ $invoice->invoice_number }}</strong><br>
                                <small>Oy: {{ $invoice->billing_period }}</small><br>
                                <small>Holat:
                                    <span class="badge bg-{{ $invoice->status == 'paid' ? 'green-lt' : 'red-lt' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
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
                    <form action="{{ route('payments.store') }}" method="POST">
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
@endsection
