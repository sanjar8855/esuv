<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;

class WaterMeter extends Model
{
    use HasFactory, RecordUserStamps;

    protected $fillable = ['customer_id', 'meter_number', 'last_reading_date', 'installation_date', 'validity_period', 'expiration_date'];

    public function setMeterNumberAttribute($value)
    {
        if ($value) {
            $cleaned = str_replace(' ', '', (string)$value);
            $length = config('water_meter.meter_number_length', 7);
            $this->attributes['meter_number'] = str_pad($cleaned, $length, '0', STR_PAD_LEFT);
        } else {
            $this->attributes['meter_number'] = null;
        }
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function readings()
    {
        return $this->hasMany(MeterReading::class);
    }
}
