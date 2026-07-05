@echo off
echo Creating languages table...
D:\Xampp\mysql\bin\mysql.exe -u root pk_live_news < "d:\Xampp\htdocs\PK-LIVE NEWS\database_update_multilang.sql"
echo.
echo If you see any errors above, you may need to run this manually in phpMyAdmin.
pause
