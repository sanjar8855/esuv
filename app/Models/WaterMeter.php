<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterMeter extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'meter_number', 'last_reading_date', 'installation_date', 'validity_period', 'expiration_date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function readings()
    {
        return $this->hasMany(MeterReading::class);
    }
}
