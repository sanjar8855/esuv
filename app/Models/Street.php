<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Street extends Model
{
    use HasFactory;

    protected $fillable = ['neighborhood_id', 'name'];

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
