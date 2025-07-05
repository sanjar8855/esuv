<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;
use App\Traits\TracksUser;

class SaasPayment extends Model
{
    use HasFactory, RecordUserStamps, TracksUser;

    protected $fillable = [
        'company_id',
        'amount',
        'payment_date',
        'payment_period',
        'payment_method',
        'notes',
        // created_by_user_id va updated_by_user_id traitlar orqali avtomatik to'ldiriladi
    ];

    /**
     * To'lov qaysi kompaniyaga tegishli ekanligini olish.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}