<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $message;
    protected $parseMode;

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
    public function __construct($chatId, $message, $parseMode = null)
    {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->parseMode = $parseMode;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $params = [
                'chat_id' => $this->chatId,
                'text' => $this->message,
            ];

            // ✅ Parse mode (ixtiyoriy)
            if ($this->parseMode) {
                $params['parse_mode'] = $this->parseMode;
            }

            Telegram::sendMessage($params);

            Log::info('Telegram message sent successfully', [
                'chat_id' => $this->chatId
            ]);

        } catch (\Telegram\Bot\Exceptions\TelegramSDKException $e) {
            Log::error('Telegram API error', [
                'chat_id' => $this->chatId,
                'error' => $e->getMessage()
            ]);

            // ✅ User bot ni bloklagan bo'lsa
            if (str_contains($e->getMessage(), 'blocked by the user')) {
                Log::warning('Bot blocked by user', ['chat_id' => $this->chatId]);
            }

            throw $e; // ✅ Qayta urinish

        } catch (\Exception $e) {
            Log::error('Failed to send telegram message', [
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
        Log::error('SendTelegramMessageJob failed permanently', [
            'chat_id' => $this->chatId,
            'error' => $exception->getMessage()
        ]);
    }
}
