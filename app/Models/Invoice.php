<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;

class Invoice extends Model
{
    use HasFactory, RecordUserStamps;

    protected $fillable = ['customer_id', 'tariff_id', 'invoice_number', 'billing_period', 'amount_due', 'due_date', 'status'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->invoice_number = self::generateUniqueInvoiceNumber();
        });
    }

    private static function generateUniqueInvoiceNumber()
    {
        do {
            $number = date('Y') . '-' . str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('invoice_number', $number)->exists()); // Takrorlanmasligi tekshiriladi

        return $number;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
