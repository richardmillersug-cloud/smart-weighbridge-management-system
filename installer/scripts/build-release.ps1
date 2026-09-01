# Builds a release folder ready for Inno Setup packaging.
# Requires: PHP, Composer, Node.js, npm
# Usage: powershell -ExecutionPolicy Bypass -File installer\scripts\build-release.ps1

$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$ReleaseDir = Join-Path $AppRoot "dist\SmartWeighbridgeRelease"

Write-Host "Building Smart Weighbridge release..." -ForegroundColor Cyan
Set-Location $AppRoot

foreach ($cmd in @("php", "composer", "npm")) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) {
        throw "$cmd is not installed or not on PATH."
    }
}

Write-Host "[1/4] Composer production install..."
if (Test-Path "composer.phar") {
    php composer.phar install --no-dev --optimize-autoloader --no-interaction
} else {
    composer install --no-dev --optimize-autoloader --no-interaction
}

Write-Host "[2/4] Frontend build..."
npm ci
npm run build

Write-Host "[3/4] Preparing release folder..."
if (Test-Path $ReleaseDir) {
    Remove-Item $ReleaseDir -Recurse -Force
}
New-Item -ItemType Directory -Path $ReleaseDir -Force | Out-Null

$excludeDirs = @(
    ".git", "node_modules", "dist", "tests", ".phpunit.cache",
    "storage\logs", "storage\framework\cache", "storage\framework\sessions",
    "storage\framework\views", "database\*.sqlite"
)

robocopy $AppRoot $ReleaseDir /MIR /XD .git node_modules dist tests .phpunit.cache `
    storage\logs storage\framework\cache storage\framework\sessions storage\framework\views `
    /XF .env .env.backup composer.phar php-local.ini database\*.sqlite `
    /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null

# Ensure writable storage dirs exist in release
@(
    "storage\logs", "storage\framework\cache", "storage\framework\sessions",
    "storage\framework\views", "storage\app\public", "storage\certs",
    "bootstrap\cache"
) | ForEach-Object {
    $path = Join-Path $ReleaseDir $_
    if (-not (Test-Path $path)) { New-Item -ItemType Directory -Path $path -Force | Out-Null }
}

Write-Host "[4/4] Release folder ready: $ReleaseDir" -ForegroundColor Green
Write-Host ""
Write-Host "Next: compile installer with Inno Setup:"
Write-Host "  installer\SmartWeighbridge.iss"
Write-Host "Output EXE will be in: dist\"
