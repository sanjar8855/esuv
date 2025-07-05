<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'customer_limit',
        'description',
        'is_active',
    ];

    /**
     * Bu tarif rejasiga tegishli kompaniyalarni olish.
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}