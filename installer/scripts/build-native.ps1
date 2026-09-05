# Build Smart Weighbridge as a native Windows .exe (NativePHP)
$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $AppRoot

$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

Write-Host "Building native Windows desktop app..." -ForegroundColor Cyan

& (Join-Path $PSScriptRoot "build-icon.ps1")
& (Join-Path $PSScriptRoot "publish-native-electron.ps1")
npm run build
php artisan native:install --no-interaction
php installer/scripts/prepare-native-ci.php
php artisan native:build win --no-interaction
& (Join-Path $PSScriptRoot "verify-native-build.ps1")

Write-Host ""
Write-Host "Native build complete. Check the dist/ folder for Smart Weighbridge.exe" -ForegroundColor Green
