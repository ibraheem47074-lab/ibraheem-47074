@echo off
REM Windows Task Scheduler Setup for RSS Auto-Import
REM This script sets up a scheduled task to run RSS import every 5 minutes

echo ========================================
echo PK Live News RSS Import Task Scheduler
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator
    echo Right-click the file and select "Run as administrator"
    pause
    exit /b 1
)

REM Get the current directory
set SCRIPT_DIR=%~dp0
set SCRIPT_DIR=%SCRIPT_DIR:~0,-1%

REM Find PHP executable path
echo Detecting PHP installation...
if exist "C:\xampp\php\php.exe" (
    set PHP_PATH=C:\xampp\php\php.exe
    echo Found PHP at: %PHP_PATH%
) else if exist "D:\xampp\php\php.exe" (
    set PHP_PATH=D:\xampp\php\php.exe
    echo Found PHP at: %PHP_PATH%
) else (
    echo ERROR: PHP not found in standard XAMPP locations
    echo Please edit this script and set PHP_PATH manually
    pause
    exit /b 1
)

echo.
echo Setting up scheduled task...
echo.

REM Remove existing task if it exists
schtasks /Delete /TN "PK Live News RSS Import" /F >nul 2>&1

REM Create the scheduled task
schtasks /Create /TN "PK Live News RSS Import" /TR "%PHP_PATH% %SCRIPT_DIR%\cron_import_news.php" /SC MINUTE /MO 5 /RU SYSTEM /RL HIGHEST /F

if %errorLevel% neq 0 (
    echo ERROR: Failed to create scheduled task
    pause
    exit /b 1
)

echo.
echo SUCCESS: Scheduled task created successfully!
echo.
echo Task Details:
echo - Name: PK Live News RSS Import
echo - Schedule: Every 5 minutes
echo - PHP: %PHP_PATH%
echo - Script: %SCRIPT_DIR%\cron_import_news.php
echo.
echo The task will now run automatically every 5 minutes.
echo You can view the task in Task Scheduler (taskschd.msc)
echo.
echo To test immediately, run: %PHP_PATH% %SCRIPT_DIR%\cron_import_news.php
echo.

pause
