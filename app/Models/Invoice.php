<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;
use Illuminate\Support\Facades\DB;
use App\Models\Scopes\CompanyScope;

class Invoice extends Model
{
    use HasFactory, RecordUserStamps;

    protected $fillable = [
        'customer_id',
        'tariff_id',
        'meter_reading_id', // ✅ meter reading orqali yaratilgan invoice lar uchun
        'invoice_number',
        'billing_period',
        'amount_due',
        'due_date',
        'status',
    ];

    protected static function booted()
    {
        parent::boot();

        // ✅ Global Scope (invoice.customer.company_id orqali)
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

        // Invoice number generator (eski kod)
        static::creating(function ($invoice) {
            $year = $invoice->created_at ? date('Y', strtotime($invoice->created_at)) : date('Y');
            $invoice->invoice_number = self::generateNextInvoiceNumberForYear((int)$year);
        });
    }

    public static function generateNextInvoiceNumberForYear(int $year)
    {
        // Atomik tarzda raqamni olish va yangilash
        $sequence = DB::transaction(function () use ($year) {
            // Yil uchun yozuvni topish yoki yaratish va bloklash (concurrency uchun)
            $row = DB::table('invoice_sequences')->where('year', $year)->lockForUpdate()->first();

            if ($row) {
                $newNumber = $row->last_number + 1;
                DB::table('invoice_sequences')->where('year', $year)->update(['last_number' => $newNumber]);
                return $newNumber;
            } else {
                // Agar bu yil uchun yozuv bo'lmasa, yangi yozuv yaratamiz
                DB::table('invoice_sequences')->insert(['year' => $year, 'last_number' => 1]);
                return 1;
            }
        });

        // Raqamni 7 xonali qilib formatlash (masalan, 0000001)
        return $year . '-' . str_pad($sequence, 8, '0', STR_PAD_LEFT);
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

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }
}
