<?php

namespace App\Jobs;

use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMeterReadingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $message;

    public function __construct($chatId, $message)
    {
        $this->chatId = $chatId;
        $this->message = $message;
    }

    public function handle()
    {
        \Log::info('Job started for chat ID: ' . $this->chatId);

        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => $this->message,
            'parse_mode' => 'HTML'
        ]);
    }
}
