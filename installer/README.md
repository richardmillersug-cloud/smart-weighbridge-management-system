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

See **[CUSTOMER-SETUP.md](../CUSTOMER-SETUP.md)** for the full customer-facing guide.

Summary:

1. Install PHP 8.4 + MySQL 8 on the station PC.
2. Create database: `CREATE DATABASE smart_weighbridge;`
3. Run **`SmartWeighbridge-Setup.exe`**.
4. On first launch, edit **`.env`** (MySQL password, COM port, DigitalOcean cloud settings).
5. Run **Station Setup** from Start Menu (or `setup-station.ps1`).
6. Place DO CA cert in `storage\certs\ca-certificate.crt`.
7. Run cloud migrations once: `php artisan migrate --database=mysql_cloud` and `php artisan cloud:sync-full`.
8. Double-click **Smart Weighbridge** desktop shortcut.

## Launcher scripts

| File | Purpose |
|------|---------|
| `SmartWeighbridge.bat` | Starts queue worker + web server + opens browser |
| `Stop SmartWeighbridge.bat` | Stops background PHP processes |
| `upgrade-station.ps1` | Migrations after installing an update |

## Without Inno Setup

You can copy the release folder directly:

```powershell
installer\scripts\build-release.ps1
# Copy dist\SmartWeighbridgeRelease to the station PC
# Create .env, run setup-station.ps1, use SmartWeighbridge.bat
```
