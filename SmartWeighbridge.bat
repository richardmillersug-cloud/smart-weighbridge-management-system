@echo off
setlocal EnableExtensions

set "APP_ROOT=%~dp0"
if "%APP_ROOT:~-1%"=="\" set "APP_ROOT=%APP_ROOT:~0,-1%"
cd /d "%APP_ROOT%"

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP 8.4+ is required and must be on PATH.
    echo Install PHP 8.4 and MySQL 8 on this PC, then start Smart Weighbridge again.
    pause
    exit /b 1
)

php -r "exit(version_compare(PHP_VERSION, '8.4.0', '>=') ? 0 : 1);"
if errorlevel 1 (
    echo [ERROR] PHP 8.4 or newer is required.
    php -v
    echo Install PHP 8.4+, add it to PATH, then start Smart Weighbridge again.
    pause
    exit /b 1
)

if not exist ".env" (
    if exist "installer\env\.env.station.example" (
        copy /Y "installer\env\.env.station.example" ".env" >nul
    ) else (
        copy /Y ".env.example" ".env" >nul
    )
    php artisan key:generate --force
)

if exist "storage\app\station-setup.complete" (
    php artisan migrate --force >nul 2>&1
)

echo Starting Smart Weighbridge...
php artisan native:run --no-interaction
exit /b %ERRORLEVEL%
