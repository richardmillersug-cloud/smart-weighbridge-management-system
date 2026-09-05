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

function Get-UnpackedDir {
    param([string]$DistRoot)

    $matches = Get-ChildItem $DistRoot -Directory -Recurse -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -match '^win(-[a-z0-9]+)?-unpacked$' }

    if ($matches) {
        return ($matches | Select-Object -First 1).FullName
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
if ($setup.Length -lt 50MB) {
    throw "Setup exe looks too small ($setupMb MB)."
}

Write-Host "Setup exe found: $($setup.FullName) ($setupMb MB)" -ForegroundColor Green

$unpacked = Get-UnpackedDir -DistRoot $dist
if (-not $unpacked) {
    Write-Host "Note: win-unpacked not present in dist (NSIS-only output). Skipping unpacked file checks." -ForegroundColor Yellow
    Write-Host "Native build verified (installer artifact only)." -ForegroundColor Green
    exit 0
}

$icuPath = Join-Path $unpacked "icudtl.dat"
$icu = if (Test-Path $icuPath) {
    $icuPath
} else {
    $found = Get-ChildItem $unpacked -Recurse -Filter "icudtl.dat" -File -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($found) { $found.FullName } else { $null }
}

if (-not $icu) {
    throw "icudtl.dat missing from native build (ICU startup will fail)."
}

$phpPath = Join-Path $unpacked "resources\build\php\php.exe"
$php = if (Test-Path $phpPath) {
    $phpPath
} else {
    $found = Get-ChildItem $unpacked -Recurse -Filter "php.exe" -File -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($found) { $found.FullName } else { $null }
}

if (-not $php) {
    throw "php.exe missing from native build."
}

Write-Host "Native build verified:" -ForegroundColor Green
Write-Host "  unpacked     -> $unpacked"
Write-Host "  icudtl.dat   -> $icu"
Write-Host "  php.exe      -> $php"
Write-Host "  setup exe    -> $($setup.FullName) ($setupMb MB)"
