<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\RecordUserStamps;
use App\Traits\TracksUser;

class Customer extends Model
{
    use HasFactory, RecordUserStamps, TracksUser;

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

    /**
     * "name" atributini o'rnatishdan oldin birinchi harflarini kattalashtiradi.
     *
     * @param  string  $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        // mb_convert_case o'zbekcha harflarni ham to'g'ri ishlaydi (masalan, "ismoil" -> "Ismoil")
        // MB_CASE_TITLE har bir so'zning birinchi harfini kattalashtiradi
        $this->attributes['name'] = mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
    }

    public function setAccountNumberAttribute($value)
    {
        if ($value) {
            $cleaned = str_replace(' ', '', (string)$value);
            $this->attributes['account_number'] = str_pad($cleaned, 7, '0', STR_PAD_LEFT);
        } else {
            $this->attributes['account_number'] = null;
        }
    }

    public function setPhoneAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['phone'] = null;
            return;
        }

        // Kiritilgan qiymatdan faqat sonlarni ajratib olamiz
        $digits = preg_replace('/[^0-9]/', '', $value);

        // Agar sonlar 9 ta bo'lsa (O'zbekiston mobil raqamlari standarti)
        if (strlen($digits) === 9) {
            // Raqamni kerakli formatga o'tkazamiz
            $formatted = preg_replace('/^(\d{2})(\d{3})(\d{2})(\d{2})$/', '($1) $2-$3-$4', $digits);
            $this->attributes['phone'] = $formatted;
        } else {
            // Agar raqam 9 xonali bo'lmasa, uni o'z holicha saqlaymiz
            $this->attributes['phone'] = $value;
        }
    }

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
