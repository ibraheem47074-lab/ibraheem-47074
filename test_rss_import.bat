@echo off
REM Test RSS Import Script
echo Testing RSS Import System...
echo.

"C:\xampp\php\php.exe" "d:\Xampp\htdocs\PK-LIVE NEWS\cron_import_news.php"

echo.
echo Test completed. Check logs\cron_import.log for details.
pause
