@echo off
setlocal EnableExtensions
cd /d "%~dp0"

if exist "smart-weighbridge-management-system.exe" (
    start "" /D "%~dp0" "smart-weighbridge-management-system.exe"
    exit /b 0
)

for %%E in ("%~dp0*.exe") do (
    echo %%~nxE | findstr /I /C:"uninstall" /C:"elevate" /C:"Uninstall" >nul
    if errorlevel 1 (
        start "" /D "%~dp0" "%%~fE"
        exit /b 0
    )
)

echo Smart Weighbridge executable was not found in this folder.
pause
exit /b 1
