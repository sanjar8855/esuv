@extends('layouts.app') {{-- Yoki sizning layout faylingiz --}}

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Xodim: {{ $user->name }}</h3>
                            <div class="card-actions">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                                    Tahrirlash
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Haqiqatan ham o‘chirmoqchimisiz?');" class="ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">O‘chirish</button>
                                </form>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">
                                    Ro'yxatga qaytish
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><strong>ID:</strong> {{ $user->id }}</p>
                            <p><strong>Ism:</strong> {{ $user->name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Telefon raqami:</strong> {{ $user->phone ?? 'Kiritilmagan' }}</p>
                            <p><strong>Lavozim:</strong> {{ $user->rank ?? '-' }}</p>

                            {{-- Foydalanuvchi turini ko'rsatish (Controller'dagi logika asosida) --}}
                            <p><strong>Foydalanuvchi turi:</strong>
                                @if ($user->hasRole('admin'))
                                    Admin
                                @elseif ($user->hasRole('company_owner'))
                                    Boshqaruv
                                @elseif ($user->hasRole('employee'))
                                    Ishchi
                                @else
                                    Noma'lum
                                @endif
                            </p>

                            @if($user->company)
                                <p><strong>Kompaniya:</strong>
                                    {{-- Agar admin bo'lsa link, bo'lmasa oddiy matn --}}
                                    @can('view', $user->company) {{-- Yoki Auth::user()->hasRole('admin') --}}
                                    <a href="{{ route('companies.show', $user->company->id) }}">{{ $user->company->name }}</a>
                                    @else
                                        {{ $user->company->name }}
                                    @endcan
                                </p>
                            @endif

                            {{-- Rollarni ko'rsatish (agar kerak bo'lsa) --}}
                            {{-- <p><strong>Rollar (bazadagi):</strong> {{ $user->roles->pluck('name')->implode(', ') }}</p> --}}

                            {{-- Fayl linki (agar mavjud bo'lsa) --}}
                            @if($user->files)
                                <p><strong>Biriktirilgan fayl:</strong> <a href="{{ Storage::url($user->files) }}" target="_blank">Ko'rish/Yuklash</a></p>
                            @endif

                            {{-- ISHGA KIRGAN SANA --}}
                            <p><strong>Ishga kirgan sana:</strong>
                                @if($user->work_start)
                                    {{-- Sanani formatlash (masalan, YYYY-MM-DD) --}}
                                    {{ \Carbon\Carbon::parse($user->work_start)->format('Y-m-d') }}
                                @else
                                    - {{-- Agar sana kiritilmagan bo'lsa --}}
                                @endif
                            </p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
