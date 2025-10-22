@echo off
echo ========================================
echo Laravel Queue Worker
echo ========================================
echo.
echo Queue worker ishga tushmoqda...
echo Ctrl+C bosib to'xtatish mumkin
echo.

php artisan queue:work --tries=3 --timeout=90

pause
