<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;
use App\Traits\TracksUser;

class MeterReading extends Model
{
    use HasFactory, RecordUserStamps, TracksUser;

    protected $fillable = ['water_meter_id', 'reading', 'reading_date', 'photo_url', 'photo', 'confirmed'];

    protected $casts = [
        'confirmed' => 'boolean',
    ];
    /**
     * @var mixed
     */
    private $waterMeter;

    public function waterMeter()
    {
        return $this->belongsTo(WaterMeter::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(Customer::class, WaterMeter::class, 'id', 'id', 'water_meter_id', 'customer_id');
    }
}
