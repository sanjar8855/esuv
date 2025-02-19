<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Telegram\Bot\Api;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $webhook = 'https://esuv.uz/api/telegram/webhook';
        $telegram->setWebhook([
            'url' => 'https://api.telegram.org/bot'.env('TELEGRAM_BOT_TOKEN').'/setWebhook?url='.$webhook
        ]);
    }
}
