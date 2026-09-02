# First-time station PC setup: migrate local DB, seed roles, link storage.
# Run from app root: powershell -ExecutionPolicy Bypass -File installer\scripts\setup-station.ps1

$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $AppRoot

Write-Host "Smart Weighbridge — station setup" -ForegroundColor Cyan
Write-Host "App root: $AppRoot"

if (-not (Test-Path ".env")) {
    Write-Host "ERROR: .env not found. Run SmartWeighbridge.bat once to create it." -ForegroundColor Red
    exit 1
}

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host "ERROR: PHP not found on PATH." -ForegroundColor Red
    exit 1
}

Write-Host "`n[1/5] Generating app key (if needed)..."
php artisan key:generate --force

Write-Host "[2/5] Running local database migrations..."
php artisan migrate --force

Write-Host "[3/5] Seeding roles, users, and default station..."
php artisan db:seed --force

Write-Host "[4/5] Linking storage..."
php artisan storage:link 2>$null

Write-Host "[5/5] Cloud sync permission migration..."
php artisan migrate --force

$certsDir = Join-Path $AppRoot "storage\certs"
if (-not (Test-Path $certsDir)) {
    New-Item -ItemType Directory -Path $certsDir -Force | Out-Null
}

Write-Host "`nSetup complete." -ForegroundColor Green
Write-Host "Next steps:"
Write-Host "  1. Edit .env — set DB_PASSWORD, cloud DB credentials, WEIGHBRIDGE_COM_PORT"
Write-Host "  2. Place DigitalOcean CA cert in storage\certs\ca-certificate.crt"
Write-Host "  3. Set DB_CLOUD_SSL_CA in .env to the full cert path"
Write-Host "  4. Run: php artisan migrate --database=mysql_cloud"
Write-Host "  5. Run: php artisan cloud:sync-full"
Write-Host "  6. Double-click SmartWeighbridge.bat to start"
