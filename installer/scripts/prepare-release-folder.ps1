# Copy application files into dist/SmartWeighbridgeRelease for Inno Setup.
param(
    [string]$AppRoot = ""
)

$ErrorActionPreference = "Stop"
if (-not $AppRoot) {
    $AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
}
Set-Location $AppRoot

$ReleaseDir = Join-Path $AppRoot "dist\SmartWeighbridgeRelease"
if (Test-Path $ReleaseDir) {
    Remove-Item $ReleaseDir -Recurse -Force
}
New-Item -ItemType Directory -Path $ReleaseDir -Force | Out-Null

$robocopyArgs = @(
    $AppRoot,
    $ReleaseDir,
    "/MIR",
    "/XD", ".git", "node_modules", "dist", "tests", ".phpunit.cache",
    "storage\logs", "storage\framework\cache", "storage\framework\sessions", "storage\framework\views",
    "/NFL", "/NDL", "/NJH", "/NJS", "/nc", "/ns", "/np"
)

$process = Start-Process -FilePath "robocopy.exe" -ArgumentList $robocopyArgs -Wait -PassThru -NoNewWindow
Write-Host "robocopy exit code: $($process.ExitCode)"

if ($process.ExitCode -ge 8) {
    throw "robocopy failed with exit code $($process.ExitCode)"
}

@(
    "storage\logs", "storage\framework\cache", "storage\framework\sessions",
    "storage\framework\views", "storage\app\public", "storage\certs", "bootstrap\cache"
) | ForEach-Object {
    $path = Join-Path $ReleaseDir $_
    if (-not (Test-Path $path)) {
        New-Item -ItemType Directory -Path $path -Force | Out-Null
    }
}

Write-Host "Release folder ready: $ReleaseDir"
