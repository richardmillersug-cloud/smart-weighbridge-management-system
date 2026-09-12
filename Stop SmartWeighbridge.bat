@echo off
echo Stopping Smart Weighbridge...

taskkill /F /FI "WINDOWTITLE eq SmartWeighbridge-http*" >nul 2>&1
taskkill /F /FI "WINDOWTITLE eq SmartWeighbridge-queue*" >nul 2>&1
wmic process where "commandline like '%%artisan queue:work%%'" delete >nul 2>&1
wmic process where "commandline like '%%artisan serve%%'" delete >nul 2>&1
wmic process where "commandline like '%%SmartWeighbridge\\edge-app%%'" delete >nul 2>&1

echo Done.
timeout /t 2 /nobreak >nul
