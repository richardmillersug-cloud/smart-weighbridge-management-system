# Run after installing a newer SmartWeighbridge-Setup.exe over an existing install.
# Usage: powershell -ExecutionPolicy Bypass -File installer\scripts\upgrade-station.ps1

$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $AppRoot

Write-Host "Smart Weighbridge — upgrade" -ForegroundColor Cyan

if (-not (Test-Path ".env")) {
    Write-Host "ERROR: .env not found. Run a fresh install first." -ForegroundColor Red
    exit 1
}

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: PHP not found on PATH." -ForegroundColor Red
    exit 1
}

Write-Host "[1/4] Stopping running app (if any)..."
& (Join-Path $AppRoot "Stop SmartWeighbridge.bat") 2>$null

Write-Host "[2/4] Running database migrations..."
php artisan migrate --force

Write-Host "[3/4] Refreshing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

Write-Host "[4/4] Upgrade complete." -ForegroundColor Green
Write-Host "Restart with SmartWeighbridge.bat"
Write-Host "Optional: php artisan cloud:sync-full"
