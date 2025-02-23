<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramAccount extends Model
{
    use HasFactory;

    protected $fillable = ['telegram_chat_id', 'username'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_telegram_account', 'telegram_account_id', 'customer_id');
    }
}
