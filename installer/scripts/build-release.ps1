# Builds a release folder ready for Inno Setup packaging.
# Requires: PHP, Composer, Node.js, npm
# Usage: powershell -ExecutionPolicy Bypass -File installer\scripts\build-release.ps1

$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$ReleaseDir = Join-Path $AppRoot "dist\SmartWeighbridgeRelease"

Write-Host "Building Smart Weighbridge release..." -ForegroundColor Cyan
Set-Location $AppRoot

foreach ($cmd in @("php", "npm")) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) {
        throw "$cmd is not installed or not on PATH."
    }
}

if (-not (Get-Command composer -ErrorAction SilentlyContinue) -and -not (Test-Path (Join-Path $AppRoot "composer.phar"))) {
    throw "composer is not installed and composer.phar was not found."
}

Write-Host "[1/4] Composer production install..."
if (Test-Path "composer.phar") {
    php composer.phar install --no-dev --optimize-autoloader --no-interaction
} else {
    composer install --no-dev --optimize-autoloader --no-interaction
}

Write-Host "[2/4] Frontend build..."
if (Test-Path "package-lock.json") {
    npm ci
} else {
    npm install
}
npm run build

Write-Host "[3/4] Preparing release folder..."
& (Join-Path $PSScriptRoot "prepare-release-folder.ps1") -AppRoot $AppRoot

Write-Host "[4/4] Release folder ready: $ReleaseDir" -ForegroundColor Green
Write-Host ""
Write-Host "Next: compile installer with Inno Setup:"
Write-Host "  installer\SmartWeighbridge.iss"
Write-Host "Output EXE will be in: dist\"
