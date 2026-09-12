# Open Smart Weighbridge in a desktop app window (no address bar).
# Uses Microsoft Edge / Chrome --app mode so Chromium ICU comes from Windows,
# not from Electron's fragile icudtl.dat.

param(
    [string]$AppUrl = "http://127.0.0.1:8000"
)

$ErrorActionPreference = "Stop"
$profile = Join-Path $env:LOCALAPPDATA "SmartWeighbridge\edge-app"
New-Item -ItemType Directory -Force -Path $profile | Out-Null

$pf = $env:ProgramFiles
$pf86 = ${env:ProgramFiles(x86)}
$candidates = @(
    (Join-Path $pf86 "Microsoft\Edge\Application\msedge.exe"),
    (Join-Path $pf "Microsoft\Edge\Application\msedge.exe"),
    (Join-Path $pf "Google\Chrome\Application\chrome.exe"),
    (Join-Path $pf86 "Google\Chrome\Application\chrome.exe")
)

foreach ($exe in $candidates) {
    if ($exe -and (Test-Path $exe)) {
        Start-Process -FilePath $exe -ArgumentList @(
            "--app=$AppUrl",
            "--user-data-dir=$profile",
            "--no-first-run",
            "--disable-extensions"
        )
        exit 0
    }
}

Start-Process $AppUrl
exit 0
