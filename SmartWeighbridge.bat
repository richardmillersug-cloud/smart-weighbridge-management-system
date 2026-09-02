@echo off
setlocal EnableExtensions

set "APP_ROOT=%~dp0"
if "%APP_ROOT:~-1%"=="\" set "APP_ROOT=%APP_ROOT:~0,-1%"
cd /d "%APP_ROOT%"

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP is not installed or not on PATH.
    echo Install PHP 8.4+ and add it to PATH, then run this again.
    pause
    exit /b 1
)

if not exist ".env" (
    echo [SETUP] Creating .env from station template...
    copy /Y "installer\env\.env.station.example" ".env" >nul
    php artisan key:generate --force
    echo.
    echo Edit .env with your MySQL password and cloud DB settings, then run:
    echo   powershell -ExecutionPolicy Bypass -File installer\scripts\setup-station.ps1
    echo.
    pause
    exit /b 0
)

echo Starting Smart Weighbridge native desktop app...
php artisan native:run --no-interaction
exit /b %ERRORLEVEL%
