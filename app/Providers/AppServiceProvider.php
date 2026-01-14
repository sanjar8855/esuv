<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

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

        Paginator::useBootstrap();

        // ✅ Observer registratsiyasi
        // Eslatma: Observer'lar `App\Providers\EventServiceProvider` ichidagi `$observers` orqali ro'yxatdan o'tkaziladi.
        // Bu yerda yana observe() qilish dublikat chaqirilishga olib kelishi mumkin.

        // ✅ Telegram WebApp layout detection
        View::composer('*', function ($view) {
            $isTelegramWebApp = session('is_telegram_webapp', false);
            $view->with('isTelegramWebApp', $isTelegramWebApp);
        });

        // ✅ Blade directive for layout selection
        Blade::directive('layout', function () {
            return "<?php echo session('is_telegram_webapp') ? 'layouts.telegram-webapp' : 'layouts.app'; ?>";
        });
    }
}
