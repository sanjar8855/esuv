<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RecordUserStamps;

class Notification extends Model
{
    use HasFactory, RecordUserStamps;

    protected $fillable = ['customer_id', 'type', 'message', 'sent_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
