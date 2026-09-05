# Publish NativePHP electron project and apply Smart Weighbridge build patches.
param(
    [string]$AppRoot = "",
    [switch]$SkipPublish
)

$ErrorActionPreference = "Stop"
if (-not $AppRoot) {
    $AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
}
Set-Location $AppRoot

if (-not $SkipPublish) {
    Write-Host "Publishing NativePHP electron project..." -ForegroundColor Cyan
    php artisan native:install --publish --force --no-interaction
} else {
    Write-Host "Applying native electron patches (skip publish)..." -ForegroundColor Cyan
}

$electron = Join-Path $AppRoot "nativephp\electron"
if (-not (Test-Path (Join-Path $electron "package.json"))) {
    throw "nativephp/electron was not published."
}

$patchDir = Join-Path $PSScriptRoot "..\native-electron"
Copy-Item (Join-Path $patchDir "electron-builder.mjs") (Join-Path $electron "electron-builder.mjs") -Force
Copy-Item (Join-Path $patchDir "php.js") (Join-Path $electron "php.js") -Force
Copy-Item (Join-Path $patchDir "index.js") (Join-Path $electron "src\main\index.js") -Force

Write-Host "Preparing extras/ for native installer..." -ForegroundColor Cyan
$extras = Join-Path $AppRoot "extras"
if (Test-Path $extras) {
    Remove-Item $extras -Recurse -Force
}
New-Item -ItemType Directory -Force -Path $extras | Out-Null

Copy-Item (Join-Path $AppRoot "CUSTOMER-SETUP.md") (Join-Path $extras "CUSTOMER-SETUP.md") -Force
Copy-Item (Join-Path $AppRoot "installer\env\.env.station.example") (Join-Path $extras ".env.station.example") -Force
Copy-Item (Join-Path $AppRoot "installer\scripts\setup-station.ps1") (Join-Path $extras "setup-station.ps1") -Force
Copy-Item (Join-Path $AppRoot "installer\scripts\upgrade-station.ps1") (Join-Path $extras "upgrade-station.ps1") -Force

@'
Smart Weighbridge — station support files (installed next to the native app).

1. Install PHP 8.4+ (on PATH) and MySQL 8
2. Launch Smart Weighbridge from the Start Menu or desktop shortcut
3. Complete the first-run setup screen (MySQL password, COM port, optional cloud sync)
'@ | Set-Content (Join-Path $extras "README.txt") -Encoding UTF8

Write-Host "NativePHP electron project published and patched." -ForegroundColor Green
