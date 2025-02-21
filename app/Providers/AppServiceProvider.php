<?php

namespace App\Providers;

use Telegram\Bot\Api;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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

//        if (app()->runningInConsole()) { // Faqat artisan buyrug‘i ishlaganda webhookni sozlash
//            $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
//
//            $webhookUrl = env('TELEGRAM_WEBHOOK_URL');
//            if ($webhookUrl) { // Faqat webhook URL mavjud bo‘lsa
//                $telegram->setWebhook(['url' => $webhookUrl]);
//            } else {
//                \Log::error('TELEGRAM_WEBHOOK_URL mavjud emas. .env faylini tekshiring!');
//            }
//        }
    }
}
