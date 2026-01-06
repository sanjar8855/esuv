@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Import Log Batafsil</h3>
                            <div class="card-actions">
                                <a href="{{ route('import_logs.index') }}" class="btn btn-outline-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <line x1="5" y1="12" x2="9" y2="16"></line>
                                        <line x1="5" y1="12" x2="9" y2="8"></line>
                                    </svg>
                                    Orqaga
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Asosiy ma'lumotlar --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">Import ID:</th>
                                            <td>{{ $importLog->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Import Turi:</th>
                                            <td>
                                                @if($importLog->import_type === 'customers')
                                                    Mijozlar
                                                @elseif($importLog->import_type === 'meter_readings')
                                                    Ko'rsatkichlar
                                                @else
                                                    {{ $importLog->import_type }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Fayl Nomi:</th>
                                            <td>{{ $importLog->file_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Foydalanuvchi:</th>
                                            <td>{{ $importLog->user ? $importLog->user->name : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kompaniya:</th>
                                            <td>{{ $importLog->company ? $importLog->company->name : '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">Holat:</th>
                                            <td>
                                                @if($importLog->status === 'processing')
                                                    <span class="badge bg-blue">Jarayonda</span>
                                                @elseif($importLog->status === 'completed')
                                                    <span class="badge bg-green">Tugallandi</span>
                                                @elseif($importLog->status === 'completed_with_errors')
                                                    <span class="badge bg-yellow">Xatoliklar bilan</span>
                                                @elseif($importLog->status === 'failed')
                                                    <span class="badge bg-red">Muvaffaqiyatsiz</span>
                                                @else
                                                    <span class="badge bg-gray">{{ $importLog->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Jami Qatorlar:</th>
                                            <td><strong>{{ $importLog->total_rows }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Muvaffaqiyatli:</th>
                                            <td><span class="badge bg-green">{{ $importLog->success_count }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Xatolar:</th>
                                            <td><span class="badge bg-red">{{ $importLog->failed_count }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Sana:</th>
                                            <td>{{ $importLog->created_at ? $importLog->created_at->format('d.m.Y H:i:s') : '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            {{-- Statistika --}}
                            @if($importLog->total_rows > 0)
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="progress">
                                            @php
                                                $successPercent = ($importLog->success_count / $importLog->total_rows) * 100;
                                                $failedPercent = ($importLog->failed_count / $importLog->total_rows) * 100;
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $successPercent }}%" aria-valuenow="{{ $successPercent }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($successPercent, 1) }}%
                                            </div>
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $failedPercent }}%" aria-valuenow="{{ $failedPercent }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($failedPercent, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Xatolar ro'yxati --}}
                            @if($importLog->failed_count > 0 && !empty($importLog->errors))
                                <div class="row">
                                    <div class="col-12">
                                        <h3 class="mb-3">Xatolar Ro'yxati ({{ $importLog->failed_count }} ta)</h3>
                                        <div class="alert alert-info">
                                            <strong>Eslatma:</strong> Bu xatolarni ko'rib chiqing va kerakli ma'lumotlarni qo'lda kiritishingiz mumkin.
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                <tr>
                                                    <th width="80">Qator</th>
                                                    <th>Xatolar</th>
                                                    <th>Ma'lumotlar</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($importLog->errors as $error)
                                                    <tr>
                                                        <td><span class="badge bg-red">{{ $error['row'] ?? '-' }}</span></td>
                                                        <td>
                                                            <div class="text-danger">
                                                                {{ $error['errors'] ?? 'Noma\'lum xato' }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if(isset($error['data']))
                                                                <details>
                                                                    <summary class="text-muted" style="cursor: pointer;">Ma'lumotlarni ko'rish</summary>
                                                                    <div class="mt-2">
                                                                        <small>
                                                                            @foreach($error['data'] as $key => $value)
                                                                                <div><strong>{{ $key }}:</strong> {{ $value ?? '-' }}</div>
                                                                            @endforeach
                                                                        </small>
                                                                    </div>
                                                                </details>
                                                            @else
                                                                <span class="text-muted">Ma'lumot yo'q</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if($importLog->success_count > 0)
                                    <div class="alert alert-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path d="M9 12l2 2l4 -4"></path>
                                        </svg>
                                        <strong>Barcha ma'lumotlar muvaffaqiyatli import qilindi!</strong> Hech qanday xato topilmadi.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
