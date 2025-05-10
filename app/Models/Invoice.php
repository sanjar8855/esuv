<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;
use App\Traits\TracksUser;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory, RecordUserStamps, TracksUser;

    protected $fillable = ['customer_id', 'tariff_id', 'invoice_number', 'billing_period', 'amount_due', 'due_date', 'status'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            // Agar created_at invoys yaratilishidan oldin o'rnatilgan bo'lsa, o'sha yilni olamiz,
            // aks holda joriy yilni olamiz.
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
}
