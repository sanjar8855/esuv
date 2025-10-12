<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Traits\RecordUserStamps;
use App\Traits\TracksUser;
use App\Models\Scopes\CompanyScope;

class Payment extends Model
{
    use HasFactory, RecordUserStamps, TracksUser;

    protected $fillable = ['customer_id', 'invoice_id', 'amount', 'payment_date', 'payment_method', 'status'];

    protected static function booted()
    {
        // ✅ Global Scope (payment.customer.company_id orqali)
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
