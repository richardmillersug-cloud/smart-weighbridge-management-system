# Verify NativePHP Windows build contains required Electron and PHP files.
param(
    [string]$AppRoot = ""
)

$ErrorActionPreference = "Stop"
if (-not $AppRoot) {
    $AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
}

$dist = Join-Path $AppRoot "nativephp\electron\dist"
if (-not (Test-Path $dist)) {
    throw "Build output not found: $dist"
}

$unpacked = Get-ChildItem $dist -Directory -Filter "win-unpacked" | Select-Object -First 1
if (-not $unpacked) {
    throw "win-unpacked folder missing under $dist"
}

$icu = Get-ChildItem $unpacked.FullName -Recurse -Filter "icudtl.dat" -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $icu) {
    throw "icudtl.dat missing from native build (ICU startup will fail)."
}

$php = Get-ChildItem $unpacked.FullName -Recurse -Filter "php.exe" -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $php) {
    throw "php.exe missing from native build."
}

$setup = Get-ChildItem $dist -Filter "*-setup.exe" -ErrorAction SilentlyContinue | Sort-Object Length -Descending | Select-Object -First 1
if (-not $setup) {
    throw "NSIS setup exe missing from native build."
}

Write-Host "Native build verified:" -ForegroundColor Green
Write-Host "  icudtl.dat -> $($icu.FullName)"
Write-Host "  php.exe      -> $($php.FullName)"
Write-Host "  setup exe    -> $($setup.FullName) ($([math]::Round($setup.Length/1MB,2)) MB)"
