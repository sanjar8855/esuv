@extends('layouts.app')

{{-- DataTables CSS endi kerak emas --}}
@push('styles')
    <style>
        /* Agar kerak bo'lsa, oddiy jadval uchun stillar qolishi mumkin */
    </style>
@endpush

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1>Barcha To'lovlar Tarixi (Jurnal)</h1>
                        <div>
                            <a href="{{ route('saas.payments.index') }}" class="btn btn-outline-info">Oylar Bo'yicha Holat</a>
                            <a href="{{ route('saas.payments.create') }}" class="btn btn-primary ms-2">Yangi To'lov Qo'shish</a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kompaniya Nomi</th>
                                    <th>To'lov Davri</th>
                                    <th>Summa (UZS)</th>
                                    <th>To'lov Sanasi</th>
                                    <th>Kim Qo'shdi</th>
                                    <th>Amallar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($saasPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td>
                                            @if($payment->company)
                                                <a href="{{ route('companies.show', $payment->company->id) }}">{{ $payment->company->name }}</a>
                                            @else
                                                <span class="text-muted">Kompaniya o'chirilgan</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->payment_period ? \Carbon\Carbon::parse($payment->payment_period . '-01')->format('F Y') : '-' }}</td>
                                        <td>{{ number_format($payment->amount, 0, '.', ' ') }}</td>
                                        <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y') : '-' }}</td>
                                        <td>{{ $payment->createdBy?->name ?? 'Noma\'lum' }}</td>
                                        <td>
                                            <a href="{{ route('saas.payments.edit', $payment->id) }}" class="btn btn-sm btn-warning">Tahrirlash</a>
                                            <form action="{{ route('saas.payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Haqiqatan ham o‘chirmoqchimisiz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">O‘chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Hali hech qanday to'lovlar kiritilmagan.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- ODDIY LARAVEL PAGINATION --}}
                        @if ($saasPayments->hasPages())
                            <div class="card-footer d-flex align-items-center">
                                {{ $saasPayments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
