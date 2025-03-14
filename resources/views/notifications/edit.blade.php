@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Xabarnomani Tahrirlash</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('notifications.update', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="customer_id">Mijoz:</label>
                            <select name="customer_id" class="form-control" required>
                                @foreach($customers as $customer)
                                    <option
                                        value="{{ $customer->id }}" {{ $notification->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="type">Xabar Turi:</label>
                            <select name="type" class="form-control" required>
                                <option value="reminder" {{ $notification->type == 'reminder' ? 'selected' : '' }}>
                                    Eslatma
                                </option>
                                <option value="alert" {{ $notification->type == 'alert' ? 'selected' : '' }}>
                                    Ogohlantirish
                                </option>
                                <option value="info" {{ $notification->type == 'info' ? 'selected' : '' }}>Ma’lumot
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="message">Xabar:</label>
                            <textarea name="message" class="form-control"
                                      required>{{ $notification->message }}</textarea>
                        </div>

                        {{--                        <div class="mb-3">--}}
                        {{--                            <label for="sent_at">Yuborilgan Sana:</label>--}}
                        {{--                            <input type="date" name="sent_at" class="form-control" value="{{ $notification->sent_at }}"--}}
                        {{--                                   required>--}}
                        {{--                        </div>--}}

                        <button type="submit" class="btn btn-primary">Yangilash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
