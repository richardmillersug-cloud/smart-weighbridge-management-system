# Open Smart Weighbridge in a desktop app window (no address bar).
# Uses Microsoft Edge / Chrome --app mode so Chromium ICU comes from Windows,
# not from Electron's fragile icudtl.dat.

param(
    [string]$AppUrl = "http://127.0.0.1:8000"
)

$ErrorActionPreference = "Stop"
$profileDir = Join-Path $env:LOCALAPPDATA "SmartWeighbridge\edge-app"
New-Item -ItemType Directory -Force -Path $profileDir | Out-Null

$pf = $env:ProgramFiles
$pf86 = ${env:ProgramFiles(x86)}
$candidates = @(
    (Join-Path $pf86 "Microsoft\Edge\Application\msedge.exe"),
    (Join-Path $pf "Microsoft\Edge\Application\msedge.exe"),
    (Join-Path $pf "Google\Chrome\Application\chrome.exe"),
    (Join-Path $pf86 "Google\Chrome\Application\chrome.exe")
)

# Quote the profile path: Windows user names often contain spaces, and an
# unquoted --user-data-dir makes the browser exit without opening a window.
$arguments = @(
    "--app=$AppUrl",
    "--user-data-dir=`"$profileDir`"",
    "--no-first-run",
    "--disable-extensions"
)

foreach ($exe in $candidates) {
    if ($exe -and (Test-Path $exe)) {
        $browser = Start-Process -FilePath $exe -ArgumentList $arguments -PassThru
        Start-Sleep -Seconds 3

        if (-not $browser.HasExited) {
            exit 0
        }

        Write-Warning "$([System.IO.Path]::GetFileName($exe)) closed without opening the window. Trying the next browser."
    }
}

Write-Warning "No desktop app window could be opened. Falling back to the default browser."
Start-Process $AppUrl
exit 0
