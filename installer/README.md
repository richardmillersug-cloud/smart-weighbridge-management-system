# Windows installer build

Package the Smart Weighbridge app as **`SmartWeighbridge-Setup.exe`** for station PCs.

## Prerequisites (build machine)

| Tool | Notes |
|------|--------|
| PHP 8.4+ | Same extensions as main README |
| Composer 2.x | |
| Node.js 20+ | For `npm run build` |
| Inno Setup 6 or 7 | [Download](https://jrsoftware.org/isinfo.php) |

## Prerequisites (station PC)

| Tool | Notes |
|------|--------|
| PHP 8.4+ | On PATH (`php -v`) |
| MySQL 8.x | Local primary database |
| COM port | XK3190-DS17 connected to same PC |

PHP and MySQL are **not bundled** in the installer — install them on the station PC first (e.g. PHP from windows.php.net, MySQL or XAMPP).

## Build steps

From the project root:

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\build-release.ps1
```

Then compile with Inno Setup (GUI) or command line:

```powershell
& "C:\Program Files\Inno Setup 7\ISCC.exe" "installer\SmartWeighbridge.iss"
```

Output: **`dist\SmartWeighbridge-Setup.exe`**

### App icon (desktop shortcut)

Source PNG: **`installer/assets/app-icon.png`**. Regenerate the Windows `.ico`:

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\build-icon.ps1
```

The installer uses **`installer/assets/app-icon.ico`** for the Setup EXE and desktop shortcut.

## Publish a GitHub Release (easy customer updates)

Version is stored in **`VERSION`** (e.g. `1.0.0`). Bump it, update **`CHANGELOG.md`**, then:

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\publish-release.ps1
```

This builds the installer and creates/updates a GitHub Release with:
- `SmartWeighbridge-Setup.exe`
- `CUSTOMER-SETUP.md`

Requires [GitHub CLI](https://cli.github.com/) (`gh auth login`). To build only without uploading:

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\publish-release.ps1 -SkipPublish
```

### Automated release (CI)

Push a version tag and GitHub Actions builds the installer automatically:

```powershell
git tag v1.0.0
git push origin v1.0.0
```

Release appears at:  
[https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases)

## Station install flow

See **[CUSTOMER-SETUP.md](../CUSTOMER-SETUP.md)** for the customer-facing guide.

Summary:

1. Install PHP 8.4 (on PATH) and MySQL 8 on the station PC.
2. Run **`SmartWeighbridge-Setup.exe`** or **`SmartWeighbridge-Native.exe`**.
3. Launch the app. First run opens the setup screen (MySQL password, COM port, optional cloud sync).
4. The app creates the database, migrates, and seeds accounts. Sign in when it finishes.

## Launcher scripts

| File | Purpose |
|------|---------|
| `SmartWeighbridge.bat` | Checks PHP, applies migrations if already set up, starts the app |
| `Stop SmartWeighbridge.bat` | Stops the desktop app and background PHP processes |
| `upgrade-station.ps1` | Optional manual migrate after an update (also runs on launch) |

## Without Inno Setup

```powershell
installer\scripts\build-release.ps1
# Copy dist\SmartWeighbridgeRelease to the station PC
# Run SmartWeighbridge.bat — complete setup in the app window
```
