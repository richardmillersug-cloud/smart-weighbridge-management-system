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

call :ensure_running "SmartWeighbridge Queue" "php artisan queue:work --tries=5 --sleep=3"
call :ensure_running "SmartWeighbridge Server" "php artisan serve --host=127.0.0.1 --port=8000"

timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:8000"
exit /b 0

:ensure_running
set "WINDOW_TITLE=%~1"
set "START_CMD=%~2"
tasklist /FI "WINDOWTITLE eq %WINDOW_TITLE%*" 2>nul | find /I "%WINDOW_TITLE%" >nul
if not errorlevel 1 exit /b 0
start "%WINDOW_TITLE%" /MIN cmd /c "cd /d \"%APP_ROOT%\" && %START_CMD%"
exit /b 0
