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
     * ✅ RELATIONLAR
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // ✅ QO'SHILDI: Kim tasdiqlagan
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ✅ QO'SHILDI: Kim yaratgan
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ✅ QO'SHILDI: Kim yangilagan
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
}
