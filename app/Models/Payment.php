<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Traits\RecordUserStamps;
use App\Traits\TracksUser;

class Payment extends Model
{
    use HasFactory, RecordUserStamps, TracksUser;

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
     * ✅ RELATIONLAR - TO'G'IRLANDI!
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // ✅ TO'G'RILANDI: Bazadagi ustun nomiga mos keladi
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ✅ TO'G'RILANDI: 'created_by' emas, 'created_by_user_id'
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ✅ TO'G'RILANDI: 'updated_by' emas, 'updated_by_user_id'
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

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

    // ✅ YANGI: To'liq sana va vaqtni formatlash
    public function getFormattedPaymentDateAttribute()
    {
        // payment_date faqat sana, lekin created_at dan vaqtni olamiz
        return $this->created_at->format('d.m.Y H:i:s');
    }
}
