@echo off
echo Stopping Smart Weighbridge services...

for /f "tokens=2" %%a in ('tasklist /FI "WINDOWTITLE eq SmartWeighbridge Server*" /FO LIST ^| find "PID:"') do taskkill /PID %%a /F >nul 2>&1
for /f "tokens=2" %%a in ('tasklist /FI "WINDOWTITLE eq SmartWeighbridge Queue*" /FO LIST ^| find "PID:"') do taskkill /PID %%a /F >nul 2>&1

REM Fallback: stop php processes bound to artisan serve / queue:work on this folder
wmic process where "commandline like '%%artisan serve%%'" delete >nul 2>&1
wmic process where "commandline like '%%artisan queue:work%%'" delete >nul 2>&1

echo Done.
timeout /t 2 /nobreak >nul
