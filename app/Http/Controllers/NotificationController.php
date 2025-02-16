<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Customer;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('customer')->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('notifications.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:reminder,alert,info',
            'message' => 'required|string',
            'sent_at' => 'required|date',
        ]);

        Notification::create($request->all());
        return redirect()->route('notifications.index')->with('success', 'Xabarnoma muvaffaqiyatli qo‘shildi!');
    }

    public function show(Notification $notification)
    {
        return view('notifications.show', compact('notification'));
    }

    public function edit(Notification $notification)
    {
        $customers = Customer::all();
        return view('notifications.edit', compact('notification', 'customers'));
    }

    public function update(Request $request, Notification $notification)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:reminder,alert,info',
            'message' => 'required|string',
            'sent_at' => 'required|date',
        ]);

        $notification->update($request->all());
        return redirect()->route('notifications.index')->with('success', 'Xabarnoma muvaffaqiyatli yangilandi!');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->route('notifications.index')->with('success', 'Xabarnoma muvaffaqiyatli o‘chirildi!');
    }
}
