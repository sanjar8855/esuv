@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Xabarnomalar</h1>
                    <a href="{{ route('notifications.create') }}" class="btn btn-primary mb-3">Yangi Xabarnoma
                        Qo‘shish</a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-vcenter card-table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mijoz</th>
                                    <th>Turi</th>
                                    <th>Xabar</th>
                                    <th>Yuborilgan Sana</th>
                                    <th>Harakatlar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->id }}</td>
                                        <td>{{ $notification->customer->name }}</td>
                                        <td>{{ ucfirst($notification->type) }}</td>
                                        <td>{{ $notification->message }}</td>
                                        <td>{{ $notification->created_at }}</td>
                                        <td>
                                            <a href="{{ route('notifications.edit', $notification->id) }}"
                                               class="btn btn-warning btn-sm">Tahrirlash</a>
                                            <form action="{{ route('notifications.destroy', $notification->id) }}"
                                                  method="POST" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
