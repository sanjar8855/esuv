<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('customer')->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    public function create()
    {
        $customers = Customer::with('telegramAccounts')
            ->whereHas('telegramAccounts')
            ->get();
        return view('notifications.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:reminder,alert,info',
            'message' => 'required|string',
            'sent_at' => 'nullable|date',
        ]);

        $validated['sent_at'] = now()->format('Y-m-d');

        $notification = Notification::create($validated);

        $customer = Customer::with('telegramAccounts')->findOrFail($request->customer_id);
        $telegramAccounts = $customer->telegramAccounts;
        $type = null;
        if($notification->type =='reminder'){
            $type = 'Eslatma';
        }
        elseif($notification->type =='alert'){
            $type = 'Ogohlantirish';
        }
        elseif($notification->type =='info'){
            $type = 'Ma`lumot';
        }
        // 3️⃣ Har bir Telegram akkauntga xabar yuborish
        foreach ($telegramAccounts as $telegramAccount) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegramAccount->telegram_chat_id, // Mijozning Telegram ID'si
                    'text' => "📢 <b>Yangi xabar, $type:</b>\n" . e($request->message), // Xabar matni
                    'parse_mode' => 'HTML',
                ]);
            } catch (\Exception $e) {
                \Log::error("Telegramga xabar yuborishda xatolik: " . $e->getMessage());
            }
        }
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
            'sent_at' => 'nullable|date',
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
