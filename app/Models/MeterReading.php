<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;

class MeterReading extends Model
{
    use HasFactory, RecordUserStamps;

    protected $fillable = ['water_meter_id', 'reading', 'reading_date', 'photo_url', 'photo', 'confirmed'];

    protected $casts = [
        'confirmed' => 'boolean',
    ];

    public function waterMeter()
    {
        return $this->belongsTo(WaterMeter::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(Customer::class, WaterMeter::class, 'id', 'id', 'water_meter_id', 'customer_id');
    }
}
