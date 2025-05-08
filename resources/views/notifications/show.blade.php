@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Xabarnoma Tafsilotlari</h1>
                    <p><strong>Mijoz:</strong> {{ $notification->customer->name }}</p>
                    <p><strong>Xabar Turi:</strong> {{ ucfirst($notification->type) }}</p>
                    <p><strong>Xabar:</strong> {{ $notification->message }}</p>
                    <p><strong>Yuborilgan Sana:</strong> {{ $notification->created_at }}</p>
                    <p><strong>Yaratgan:</strong> {{ $notification->createdBy->name ?? 'Noma’lum' }}</p>
                    <p><strong>Tahrir qilgan:</strong> {{ $notification->updatedBy->name ?? 'Noma’lum' }}</p>

                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary">Ortga</a>
                </div>
            </div>
        </div>
    </div>
@endsection
