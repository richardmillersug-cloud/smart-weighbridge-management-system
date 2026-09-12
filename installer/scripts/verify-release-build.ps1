# Fails the release when an Electron runtime creeps back into the shipped app.
# Electron loaded Chromium ICU at process start and crashed with
# "Invalid file descriptor to ICU data received" on station PCs.

$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $AppRoot

$setupExe = Join-Path $AppRoot "dist\SmartWeighbridge-Setup.exe"
if (-not (Test-Path $setupExe)) {
    throw "Missing installer: $setupExe"
}

$releaseDir = Join-Path $AppRoot "dist\SmartWeighbridgeRelease"
if (-not (Test-Path $releaseDir)) {
    throw "Missing release folder: $releaseDir"
}

$banned = @("icudtl.dat", "electron.exe", "*.asar")
foreach ($pattern in $banned) {
    $hits = Get-ChildItem $releaseDir -Recurse -Filter $pattern -File -ErrorAction SilentlyContinue
    if ($hits) {
        throw "Electron artifact '$pattern' found in the release folder: $($hits[0].FullName)"
    }
}

$launcher = Join-Path $releaseDir "SmartWeighbridge.bat"
if (-not (Test-Path $launcher)) {
    throw "Missing launcher in release folder: $launcher"
}

if ((Get-Content $launcher -Raw) -match "native:run") {
    throw "SmartWeighbridge.bat still starts Electron via 'artisan native:run'."
}

$sizeMb = [math]::Round((Get-Item $setupExe).Length / 1MB, 2)
Write-Host "Installer verified, no Electron runtime: $setupExe ($sizeMb MB)" -ForegroundColor Green
