@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h2>Hisob-faktura ma’lumotlari</h2>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Hisob-faktura № {{ $invoice->invoice_number }}</h4>
                            <ul class="list-group">
                                <li class="list-group-item"><strong>Mijoz:</strong>
                                    <a href="{{ route('customers.show', $invoice->customer->id) }}"
                                       class="badge badge-outline text-blue">
                                        {{ $invoice->customer->name }}
                                    </a>
                                </li>
                                <li class="list-group-item">
                                    <strong>Tarif:</strong>
                                    m3: {{ $invoice->tariff->price_per_m3 }},
                                    meyoriy 1 kishiga: {{$invoice->tariff->for_one_person}}
                                </li>
                                <li class="list-group-item"><strong>Davr:</strong> {{ $invoice->billing_period }}</li>
                                <li class="list-group-item">
                                    <strong>Summa:</strong> {{ number_format($invoice->amount_due, 0, '.', ' ') }} UZS
                                </li>
                                <li class="list-group-item"><strong>To‘lov muddati:</strong> {{ $invoice->due_date }} </li>
                                <li class="list-group-item">
                                    <p><strong>Yaratgan:</strong> {{ $invoice->createdBy->name ?? 'Noma’lum' }}</p>
                                </li>
                                <li class="list-group-item">
                                    <p><strong>Tahrir qilgan:</strong> {{ $invoice->updatedBy->name ?? 'Noma’lum' }}</p>
                                </li>
                                <li class="list-group-item"><strong>Holat:</strong>
                                    @if($invoice->status == 'pending')
                                        <span class="badge bg-yellow text-yellow-fg">To'liq to‘lanmagan</span>
                                    @elseif($invoice->status == 'paid')
                                        <span class="badge bg-green text-green-fg">To‘langan</span>
                                    @elseif($invoice->status == 'overdue')
                                        <span class="badge bg-red text-red-fg">Muddati o‘tgan</span>
                                    @endif
                                </li>
                            </ul>
                            <div class="mt-3">
                                <a href="{{ route('invoices.edit', $invoice->id) }}"
                                   class="btn btn-warning">Tahrirlash</a>
                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
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
                                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Orqaga</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
