<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CompanyScope;
use App\Traits\RecordUserStamps;
use App\Traits\TracksUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

    protected $casts = [
        'is_active' => 'boolean',
        'has_water_meter' => 'boolean',
        'balance' => 'decimal:2',
        'family_members' => 'integer',
    ];

    /**
     * ✅ Name mutator - har bir so'zning birinchi harfini katta qilish
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
    }

    /**
     * ✅ Account number mutator
     */
    public function setAccountNumberAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['account_number'] = null;
            return;
        }

        $cleaned = str_replace(' ', '', (string)$value);

        // ✅ Faqat 7 xonadan qisqa bo'lsa 0 qo'shish
        if (strlen($cleaned) < 7) {
            $this->attributes['account_number'] = str_pad($cleaned, 7, '0', STR_PAD_LEFT);
        } else {
            $this->attributes['account_number'] = $cleaned;
        }
    }

    /**
     * ✅ Phone mutator - format: (99) 123-45-67
     */
    public function setPhoneAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['phone'] = null;
            return;
        }

        $digits = preg_replace('/[^0-9]/', '', $value);

        if (strlen($digits) === 9) {
            $this->attributes['phone'] = preg_replace(
                '/^(\d{2})(\d{3})(\d{2})(\d{2})$/',
                '($1) $2-$3-$4',
                $digits
            );
        } else {
            $this->attributes['phone'] = $value;
        }
    }

    /**
     * ✅ Model events
     */
    protected static function booted()
    {
        // 1. Global Scope
        static::addGlobalScope(new CompanyScope());

        // 2. Creating event - company_id avtomatik
        static::creating(function ($customer) {
            if (auth()->check() && !$customer->company_id) {
                $customer->company_id = auth()->user()->company_id;
            }
        });

        // 3. Deleting event - cascade delete
        static::deleting(function ($customer) {
            // PDF fayl
            if ($customer->pdf_file) {
                Storage::disk('public')->delete($customer->pdf_file);
            }

            // WaterMeter va readings
            if ($customer->waterMeter) {
                $customer->waterMeter->readings()->delete();
                $customer->waterMeter->delete();
            }

            // Invoices va Payments
            $customer->invoices()->delete();
            $customer->payments()->delete();

            // Telegram accounts
            $customer->telegramAccounts()->detach();

            Log::info('Customer deleted with related data', [
                'customer_id' => $customer->id
            ]);
        });
    }

    /**
     * ✅ Relationlar
     */
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
        return $this->belongsToMany(
            TelegramAccount::class,
            'customer_telegram_account',
            'customer_id',
            'telegram_account_id'
        );
    }

    /**
     * ✅ Balansni yangilash
     * Observer lardan chaqiriladi (InvoiceObserver, PaymentObserver)
     */
    public function updateBalance(): void
    {
        $totalDue = $this->invoices()
            ->whereIn('status', ['pending', 'unpaid', 'overdue'])
            ->sum('amount_due');

        $totalPaid = $this->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $newBalance = $totalPaid - $totalDue;

        // ✅ Faqat o'zgarganda saqlash
        if ($this->balance != $newBalance) {
            $this->balance = $newBalance;
            $this->saveQuietly(); // ✅ Observer siz

            Log::info('Customer balance updated', [
                'customer_id' => $this->id,
                'new_balance' => $newBalance
            ]);
        }
    }

    /**
     * ✅ Helper metodlar
     */
    public function getTotalDue()
    {
        return $this->invoices()
            ->whereIn('status', ['pending', 'unpaid', 'overdue'])
            ->sum('amount_due');
    }

    public function getTotalPaid()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * ✅ Scope: Aktiv mijozlar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * ✅ Scope: Qarzdorlar
     */
    public function scopeWithDebt($query)
    {
        return $query->where('balance', '<', 0);
    }
}
