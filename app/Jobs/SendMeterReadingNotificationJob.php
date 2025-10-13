<?php

namespace App\Jobs;

use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMeterReadingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $message;

    /**
     * Qayta urinishlar soni
     */
    public $tries = 3;

    /**
     * Timeout
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($chatId, $message)
    {
        $this->chatId = $chatId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Meter reading notification job started', [
            'chat_id' => $this->chatId
        ]);

        try {
            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $this->message,
                'parse_mode' => 'HTML'
            ]);

            Log::info('Meter reading notification sent successfully', [
                'chat_id' => $this->chatId
            ]);

        } catch (\Telegram\Bot\Exceptions\TelegramSDKException $e) {
            // ✅ Telegram API xatolari
            Log::error('Telegram API error in meter reading notification', [
                'chat_id' => $this->chatId,
                'error' => $e->getMessage()
            ]);

            // ✅ Agar user bot ni bloklagan bo'lsa
            if (str_contains($e->getMessage(), 'blocked by the user')) {
                Log::warning('Bot blocked by user', ['chat_id' => $this->chatId]);
                // TODO: TelegramAccount ni deactivate qilish
            }

            throw $e; // ✅ Qayta urinish uchun

        } catch (\Exception $e) {
            // ✅ Boshqa xatolar
            Log::error('Failed to send meter reading notification', [
                'chat_id' => $this->chatId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * ✅ Job fail bo'lganda
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SendMeterReadingNotificationJob failed permanently', [
            'chat_id' => $this->chatId,
            'error' => $exception->getMessage()
        ]);
    }
}
