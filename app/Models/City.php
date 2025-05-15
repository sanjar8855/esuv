<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['region_id', 'name', 'company_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
