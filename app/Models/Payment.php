<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'invoice_id', 'amount', 'payment_date', 'payment_method', 'status'];

    protected $dates = ['payment_date'];

    public function getPaymentDateAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
