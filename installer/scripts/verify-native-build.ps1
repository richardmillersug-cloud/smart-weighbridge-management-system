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

    $direct = Get-ChildItem $DistRoot -Directory -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -match '^win(-[a-z0-9]+)?-unpacked$' } |
        Select-Object -First 1
    if ($direct) {
        return $direct.FullName
    }

    $nested = Get-ChildItem $DistRoot -Directory -Recurse -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -match '^win(-[a-z0-9]+)?-unpacked$' } |
        Select-Object -First 1
    if ($nested) {
        return $nested.FullName
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

$unpacked = Get-UnpackedDir -DistRoot $dist
if (-not $unpacked) {
    Write-Host "Dist contents:" -ForegroundColor Yellow
    Get-ChildItem $dist -Force | ForEach-Object { Write-Host "  $($_.Name)" }
    throw "win-unpacked folder missing under $dist"
}

$icu = @(
    Join-Path $unpacked "icudtl.dat"
    (Get-ChildItem $unpacked -Recurse -Filter "icudtl.dat" -File -ErrorAction SilentlyContinue | Select-Object -First 1).FullName
) | Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1

if (-not $icu) {
    throw "icudtl.dat missing from native build (ICU startup will fail)."
}

$phpCandidates = @(
    Join-Path $unpacked "resources\build\php\php.exe",
    (Get-ChildItem $unpacked -Recurse -Filter "php.exe" -File -ErrorAction SilentlyContinue | Select-Object -First 1).FullName
) | Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1

if (-not $phpCandidates) {
    throw "php.exe missing from native build."
}
$php = $phpCandidates

$setup = Find-SetupExe -DistRoot $dist
if (-not $setup) {
    throw "NSIS setup exe missing from native build."
}

if ($setup.Length -lt 50MB) {
    throw "Setup exe looks too small ($([math]::Round($setup.Length/1MB,2)) MB)."
}

Write-Host "Native build verified:" -ForegroundColor Green
Write-Host "  unpacked     -> $unpacked"
Write-Host "  icudtl.dat   -> $icu"
Write-Host "  php.exe      -> $php"
Write-Host "  setup exe    -> $($setup.FullName) ($([math]::Round($setup.Length/1MB,2)) MB)"
