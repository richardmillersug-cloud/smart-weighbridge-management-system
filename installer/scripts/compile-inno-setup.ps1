# Compile installer/SmartWeighbridge.iss into dist/SmartWeighbridge-Setup.exe.
# Installs Inno Setup with Chocolatey when ISCC.exe is not already present.

$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $AppRoot

function Find-Iscc {
    $candidates = @(
        "${env:ProgramFiles(x86)}\Inno Setup 6\ISCC.exe",
        "$env:ProgramFiles\Inno Setup 6\ISCC.exe",
        "${env:ProgramFiles(x86)}\Inno Setup 7\ISCC.exe",
        "$env:ProgramFiles\Inno Setup 7\ISCC.exe"
    )

    $found = $candidates | Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1
    if ($found) { return $found }

    $onPath = Get-Command ISCC.exe -ErrorAction SilentlyContinue
    if ($onPath) { return $onPath.Source }

    return $null
}

$iscc = Find-Iscc

if (-not $iscc) {
    if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
        throw "Inno Setup is not installed and Chocolatey is unavailable. Install Inno Setup 6 from https://jrsoftware.org/isinfo.php"
    }

    Write-Host "Installing Inno Setup..." -ForegroundColor Cyan
    choco install innosetup -y --no-progress
    if ($LASTEXITCODE -ne 0) {
        throw "choco install innosetup failed with exit code $LASTEXITCODE"
    }

    $iscc = Find-Iscc
}

if (-not $iscc) {
    throw "ISCC.exe was still not found after installing Inno Setup."
}

Write-Host "Compiling installer with $iscc" -ForegroundColor Cyan
& $iscc (Join-Path $AppRoot "installer\SmartWeighbridge.iss")
if ($LASTEXITCODE -ne 0) {
    throw "Inno Setup compilation failed with exit code $LASTEXITCODE"
}

$setupExe = Join-Path $AppRoot "dist\SmartWeighbridge-Setup.exe"
if (-not (Test-Path $setupExe)) {
    throw "Setup EXE was not created at $setupExe"
}

$sizeMb = [math]::Round((Get-Item $setupExe).Length / 1MB, 2)
Write-Host "Built: $setupExe ($sizeMb MB)" -ForegroundColor Green
