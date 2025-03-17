<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'plan', 'address', 'logo', 'schet', 'inn', 'description', 'is_active'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function tariffs()
    {
        return $this->hasMany(Tariff::class);
    }
}
