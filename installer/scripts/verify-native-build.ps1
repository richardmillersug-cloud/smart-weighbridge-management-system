# Verify NativePHP Windows build contains required Electron and PHP files.
param(
    [string]$AppRoot = ""
)

$ErrorActionPreference = "Stop"
if (-not $AppRoot) {
    $AppRoot = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path
}

function Get-NativeDistRoot {
    param([string]$Root)

    $candidates = @(
        (Join-Path $Root "nativephp\electron\dist"),
        (Join-Path $Root "vendor\nativephp\desktop\resources\electron\dist"),
        (Join-Path $Root "dist")
    )

    foreach ($candidate in $candidates) {
        if (Test-Path $candidate) {
            return (Resolve-Path $candidate).Path
        }
    }

    return $null
}

function Find-SetupExe {
    param([string]$DistRoot)

    $patterns = @("*-setup.exe", "* Setup *.exe")
    foreach ($pattern in $patterns) {
        $setup = Get-ChildItem $DistRoot -Filter $pattern -File -ErrorAction SilentlyContinue |
            Sort-Object Length -Descending |
            Select-Object -First 1
        if ($setup) {
            return $setup
        }
    }

    return Get-ChildItem $DistRoot -Recurse -File -ErrorAction SilentlyContinue |
        Where-Object { $_.Extension -eq ".exe" -and $_.Name -notmatch "elevate|hiddeninput|uninstall" } |
        Sort-Object Length -Descending |
        Select-Object -First 1
}

$dist = Get-NativeDistRoot -Root $AppRoot
if (-not $dist) {
    throw "Native build output not found. Checked nativephp/electron/dist, vendor electron dist, and dist/."
}

Write-Host "Checking native build under: $dist"

$setup = Find-SetupExe -DistRoot $dist
if (-not $setup) {
    Write-Host "Dist contents:" -ForegroundColor Yellow
    Get-ChildItem $dist -Force | ForEach-Object { Write-Host "  $($_.Name)" }
    throw "NSIS setup exe missing from native build."
}

$setupMb = [math]::Round($setup.Length / 1MB, 2)
if ($setup.Length -lt 5MB) {
    throw "Setup exe looks too small ($setupMb MB)."
}

Write-Host "Setup exe found: $($setup.FullName) ($setupMb MB)" -ForegroundColor Green

Write-Host "Native build verified (installer present). PHP is provided by the station PATH, not this package." -ForegroundColor Green
Write-Host "  setup exe    -> $($setup.FullName) ($setupMb MB)"
