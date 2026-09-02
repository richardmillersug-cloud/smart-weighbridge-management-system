# Build installer and publish a GitHub Release for easy customer updates.
# Usage:
#   powershell -ExecutionPolicy Bypass -File installer\scripts\publish-release.ps1
#   powershell -ExecutionPolicy Bypass -File installer\scripts\publish-release.ps1 -Version 1.0.1
#   powershell -ExecutionPolicy Bypass -File installer\scripts\publish-release.ps1 -SkipPublish

param(
    [string]$Version = "",
    [switch]$SkipPublish
)

$ErrorActionPreference = "Stop"
$AppRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $AppRoot

$VersionFile = Join-Path $AppRoot "VERSION"
$VersionIss = Join-Path $AppRoot "installer\version.iss"
$IsccCandidates = @(
    "${env:ProgramFiles(x86)}\Inno Setup 6\ISCC.exe",
    "$env:ProgramFiles\Inno Setup 6\ISCC.exe",
    "$env:ProgramFiles\Inno Setup 7\ISCC.exe"
)

if (-not $Version) {
    $Version = (Get-Content $VersionFile -Raw).Trim()
}

if ($Version -notmatch '^\d+\.\d+\.\d+$') {
    throw "Version must be semver like 1.0.0 (got: $Version)"
}

Write-Host "Publishing Smart Weighbridge v$Version" -ForegroundColor Cyan

Set-Content -Path $VersionFile -Value $Version -NoNewline
Set-Content -Path $VersionIss -Value @(
    "; Auto-synced from root VERSION file by publish-release.ps1",
    "#define AppVersion `"$Version`""
)

& (Join-Path $PSScriptRoot "build-release.ps1")

$Iscc = $IsccCandidates | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $Iscc) {
    throw "Inno Setup ISCC.exe not found. Install Inno Setup 6 or 7."
}

Write-Host "Compiling installer with $Iscc..." -ForegroundColor Cyan
& $Iscc (Join-Path $AppRoot "installer\SmartWeighbridge.iss")

$SetupExe = Join-Path $AppRoot "dist\SmartWeighbridge-Setup.exe"
if (-not (Test-Path $SetupExe)) {
    throw "Setup EXE was not created at $SetupExe"
}

$NotesFile = Join-Path $AppRoot "dist\release-notes.md"
$Tag = "v$Version"
$ReleaseUrl = "https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/tag/$Tag"

@(
    "# Smart Weighbridge $Tag",
    "",
    "Download **SmartWeighbridge-Setup.exe** below and run it on the station PC.",
    "",
    "## New install",
    "Follow **CUSTOMER-SETUP.md** (included in the release).",
    "",
    "## Update existing install",
    "1. Stop the app (**Stop Smart Weighbridge** from Start Menu).",
    "2. Run the new **SmartWeighbridge-Setup.exe** (install over the existing folder).",
    "3. Run **Upgrade Station** from Start Menu, or:",
    "   ``powershell -ExecutionPolicy Bypass -File `"`$env:ProgramFiles\SmartWeighbridge\installer\scripts\upgrade-station.ps1`"``",
    "4. Start **Smart Weighbridge** again.",
    "",
    "Your ``.env`` and local database are kept.",
    "",
    "See CHANGELOG.md in the repository for details."
) | Set-Content -Path $NotesFile -Encoding UTF8

Write-Host ""
Write-Host "Built: $SetupExe" -ForegroundColor Green
Write-Host "Size:  $([math]::Round((Get-Item $SetupExe).Length / 1MB, 2)) MB"

if ($SkipPublish) {
    Write-Host "SkipPublish set — upload manually to GitHub Releases as $Tag"
    exit 0
}

if (-not (Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Host "GitHub CLI (gh) not found. Install from https://cli.github.com/" -ForegroundColor Yellow
    Write-Host "Manual upload: gh release create $Tag `"$SetupExe`" CUSTOMER-SETUP.md --notes-file `"$NotesFile`""
    exit 0
}

$ghAuth = gh auth status 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "Run: gh auth login" -ForegroundColor Yellow
    Write-Host "Then: gh release create $Tag `"$SetupExe`" CUSTOMER-SETUP.md --title `"Smart Weighbridge $Tag`" --notes-file `"$NotesFile`""
    exit 0
}

Write-Host "Creating GitHub Release $Tag..." -ForegroundColor Cyan
gh release view $Tag 2>$null
if ($LASTEXITCODE -eq 0) {
    gh release upload $Tag $SetupExe CUSTOMER-SETUP.md --clobber
    gh release edit $Tag --notes-file $NotesFile --title "Smart Weighbridge $Tag"
    Write-Host "Updated existing release: $ReleaseUrl" -ForegroundColor Green
} else {
    gh release create $Tag $SetupExe CUSTOMER-SETUP.md --title "Smart Weighbridge $Tag" --notes-file $NotesFile
    Write-Host "Created release: $ReleaseUrl" -ForegroundColor Green
}

Write-Host ""
Write-Host "Customers download from:" -ForegroundColor Cyan
Write-Host $ReleaseUrl
