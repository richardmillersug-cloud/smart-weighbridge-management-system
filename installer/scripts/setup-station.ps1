# First-run setup is now inside the app. This script only starts it.
$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $AppRoot

$launcher = Join-Path $AppRoot "SmartWeighbridge.bat"
if (-not (Test-Path $launcher)) {
    Write-Host "ERROR: SmartWeighbridge.bat not found." -ForegroundColor Red
    exit 1
}

Write-Host "Starting Smart Weighbridge. Complete setup in the app window." -ForegroundColor Cyan
& $launcher
