# Find the NativePHP Windows installer and copy it to dist/SmartWeighbridge-Native.exe
param(
    [string]$AppRoot = ""
)

$ErrorActionPreference = "Stop"
if (-not $AppRoot) {
    $AppRoot = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path
}

$searchDirs = @(
    (Join-Path $AppRoot "nativephp\electron\dist"),
    (Join-Path $AppRoot "vendor\nativephp\desktop\resources\electron\dist"),
    (Join-Path $AppRoot "dist")
)

Write-Host "Searching for native installer..." -ForegroundColor Cyan

foreach ($dir in $searchDirs) {
    if (Test-Path $dir) {
        Write-Host "=== $dir ==="
        Get-ChildItem $dir -Recurse -File -ErrorAction SilentlyContinue |
            Select-Object -First 80 |
            ForEach-Object { Write-Host ("  {0:n1} MB  {1}" -f ($_.Length / 1MB), $_.FullName) }
    } else {
        Write-Host "missing: $dir"
    }
}

$setup = $null
foreach ($dir in $searchDirs) {
    if (-not (Test-Path $dir)) {
        continue
    }

    $setup = Get-ChildItem $dir -Recurse -File -ErrorAction SilentlyContinue |
        Where-Object {
            $_.Extension -eq ".exe" -and
            $_.Name -notmatch "elevate|hiddeninput|uninstall" -and
            $_.Length -ge 5MB
        } |
        Sort-Object Length -Descending |
        Select-Object -First 1

    if ($setup) {
        break
    }
}

if (-not $setup) {
    throw "Native build produced no setup .exe. Stock NativePHP packaging must finish (do not replace electron-builder.mjs / php.js)."
}

$outDir = Join-Path $AppRoot "dist"
New-Item -ItemType Directory -Force -Path $outDir | Out-Null
$dest = Join-Path $outDir "SmartWeighbridge-Native.exe"
Copy-Item $setup.FullName $dest -Force

$mb = [math]::Round($setup.Length / 1MB, 2)
Write-Host "Release artifact: $dest ($mb MB) from $($setup.FullName)" -ForegroundColor Green
