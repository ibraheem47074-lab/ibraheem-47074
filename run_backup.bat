@echo off
echo Starting backup process...
cd /d "d:\Xampp\htdocs\PK-LIVE NEWS"
"C:\xampp\php\php.exe" backup_all_articles.php
pause
