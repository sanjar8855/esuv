<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'name', 'company_id'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function streets()
    {
        return $this->hasMany(Street::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
