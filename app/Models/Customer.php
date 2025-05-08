<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\RecordUserStamps;

class Customer extends Model
{
    use HasFactory, RecordUserStamps;

    protected $fillable = [
        'company_id',
        'street_id',
        'name',
        'phone',
        'address',
        'account_number',
        'has_water_meter',
        'family_members',
        'is_active',
        'balance',
        'pdf_file'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function street()
    {
        return $this->belongsTo(Street::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function waterMeter()
    {
        return $this->hasOne(WaterMeter::class);
    }

    public function telegramAccounts()
    {
        return $this->belongsToMany(TelegramAccount::class, 'customer_telegram_account', 'customer_id', 'telegram_account_id');
    }

    // 🔹 Jami qarzdorlikni hisoblash
    protected function totalDue(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->invoices()->sum('amount_due')
        );
    }

    // 🔹 Jami to‘langan miqdorni hisoblash
    protected function totalPaid(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->payments()->sum('amount')
        );
    }

    // 🔹 Balansni avtomatik hisoblash
    public function getBalanceAttribute()
    {
        $totalDue = $this->invoices()->sum('amount_due');
        $totalPaid = $this->payments()->sum('amount');

        return $totalPaid - $totalDue; // **Ortib qolgan to‘lov +, yetishmayotgan - bo‘lishi kerak**
    }

    public function updateBalance()
    {
        $totalDue = $this->invoices()
            ->whereIn('status', ['pending', 'unpaid'])
            ->sum('amount_due');

        $totalPaid = $this->payments()
            ->where('status', 'completed')
            ->sum('amount');

        // **Faqat haqiqatdan ham o‘zgarish bo‘lsa `save()` chaqiramiz**
        if ($this->balance != ($totalPaid - $totalDue)) {
            $this->balance = $totalPaid - $totalDue;
            $this->attributes['balance'] = $this->balance; // **DB ga yozishni oldini oladi**
            self::withoutEvents(function () {
                $this->save(); // **Cheksiz aylanishni oldini oladi**
            });
        }
    }

    // 🔹 Avtomatik balans yangilash (invoice yoki payment qo‘shilganda)
    protected static function boot()
    {
        parent::boot();

        static::created(function ($customer) {
            $customer->updateBalance();
        });

        static::updated(function ($customer) {
            // **Agar faqat to‘lov yoki invoice o‘zgargan bo‘lsa, balansni yangilaymiz**
            if ($customer->wasChanged(['balance'])) {
                return;
            }
            $customer->updateBalance();
        });
    }
}
