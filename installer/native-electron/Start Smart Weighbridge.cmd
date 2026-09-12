@echo off
setlocal EnableExtensions
cd /d "%~dp0"

if exist "icudtl.dat" (
    powershell -NoProfile -ExecutionPolicy Bypass -Command "try { Unblock-File -LiteralPath '%~dp0icudtl.dat' } catch {}"
)

set "EXE="
if exist "%~dp0smart-weighbridge-management-system.exe" set "EXE=%~dp0smart-weighbridge-management-system.exe"
if not defined EXE (
    for %%E in ("%~dp0*.exe") do (
        echo %%~nxE | findstr /I /C:"uninstall" /C:"elevate" /C:"Uninstall" >nul
        if errorlevel 1 set "EXE=%%~fE"
    )
)

if not defined EXE (
    echo Smart Weighbridge executable was not found in this folder.
    pause
    exit /b 1
)

start "" /D "%~dp0" "%EXE%" --icu-data-dir="%~dp0"
exit /b 0
