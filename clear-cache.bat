@echo off
echo ========================================
echo Laravel Cache Tozalash
echo ========================================
echo.

echo [1/5] Config cache tozalash...
php artisan config:clear

echo [2/5] Route cache tozalash...
php artisan route:clear

echo [3/5] View cache tozalash...
php artisan view:clear

echo [4/5] Application cache tozalash...
php artisan cache:clear

echo [5/5] Autoload yangilash...
composer dump-autoload

echo.
echo ========================================
echo Cache muvaffaqiyatli tozalandi!
echo ========================================
pause
