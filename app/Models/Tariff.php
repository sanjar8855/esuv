<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CompanyScope;


class Tariff extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'price_per_m3', 'for_one_person', 'valid_from', 'valid_to', 'is_active'];

    protected static function booted()
    {
        // ✅ Global Scope
        static::addGlobalScope(new CompanyScope());

        // ✅ Yangi tarifda avtomatik company_id
        static::creating(function ($tariff) {
            if (auth()->check() && !$tariff->company_id) {
                $tariff->company_id = auth()->user()->company_id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
