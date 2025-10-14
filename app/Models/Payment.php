<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;

class Payment extends Model
{
    use HasFactory, RecordUserStamps;  // ✅ Faqat bitta trait

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'confirmed',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    /**
     * ✅ GLOBAL SCOPE: Faqat o'z kompaniyasining to'lovlarini ko'rish
     */
    protected static function booted()
    {
        static::addGlobalScope(new class implements \Illuminate\Database\Eloquent\Scope {
            public function apply($builder, $model)
            {
                if (auth()->check() && !auth()->user()->hasRole('admin')) {
                    $builder->whereHas('customer', function($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }
            }
        });
    }

    /**
     * ✅ RELATIONSHIPS
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // ✅ Kim tasdiqlagan (bu alohida, RecordUserStamps'dan tashqari)
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ✅ createdBy va updatedBy - RecordUserStamps traitida!
    // Bu yerda yozmaslik kerak, chunki traitda bor

    /**
     * ✅ SCOPES
     */
    public function scopeConfirmed($query)
    {
        return $query->where('confirmed', true);
    }

    public function scopePending($query)
    {
        return $query->where('confirmed', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    /**
     * ✅ ACCESSORS
     */
    public function getPaymentMethodNameAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Naqd pul',
            'card' => 'Plastik karta',
            'transfer' => 'Bank o\'tkazmasi',
            'online' => 'Onlayn to\'lov',
            default => 'Noma\'lum'
        };
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, '.', ' ') . ' UZS';
    }

    // ✅ To'lov vaqti (created_at'dan)
    public function getPaymentTimeAttribute()
    {
        return $this->created_at->format('d.m.Y H:i');
    }
}

/*
📌 MUHIM:
1. ❌ TracksUser traitini olib tashladik
2. ✅ Faqat RecordUserStamps ishlatamiz
3. ✅ createdBy() va updatedBy() metodlarini bu yerda yozmaslik kerak
4. ✅ Ular RecordUserStamps traitida avtomatik qo'shiladi

📌 BARCHA BOSHQA MODELLARDA HAM SHUNDAY:
- use RecordUserStamps; qo'shing
- TracksUser traitini olib tashlang
- createdBy() va updatedBy() metodlarini o'chiring
*/
