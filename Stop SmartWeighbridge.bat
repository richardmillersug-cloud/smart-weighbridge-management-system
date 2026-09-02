@echo off
echo Stopping Smart Weighbridge...

taskkill /F /IM "Smart Weighbridge.exe" >nul 2>&1
taskkill /F /IM "electron.exe" /FI "WINDOWTITLE eq Smart Weighbridge*" >nul 2>&1
wmic process where "commandline like '%%artisan native:run%%'" delete >nul 2>&1
wmic process where "commandline like '%%artisan queue:work%%'" delete >nul 2>&1
wmic process where "commandline like '%%artisan serve%%'" delete >nul 2>&1

echo Done.
timeout /t 2 /nobreak >nul
