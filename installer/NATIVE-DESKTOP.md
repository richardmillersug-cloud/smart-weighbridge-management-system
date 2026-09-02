# Native Windows desktop app (NativePHP)

Smart Weighbridge runs as a **native Windows desktop window** (no browser address bar) using [NativePHP](https://nativephp.com/).

**MySQL remains the local database** on the station PC — the native app connects via `.env` the same way as before.

## Development (on the station/build PC)

```powershell
# First time only
php artisan native:install
npm run build

# Launch native desktop window
php artisan native:run
```

Or double-click **`SmartWeighbridge.bat`** (calls `native:run`).

Cloud sync queue workers start automatically via `config/nativephp.php` → `queue_workers`.

## Build production `.exe` for customers

Requirements: PHP 8.4, Composer, Node.js 22+, npm.

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\build-icon.ps1
npm run build
php artisan native:build win
```

Output: a **Smart Weighbridge.exe** installer/setup under `dist/` (NativePHP + Electron bundle).

Share that `.exe` with customers instead of the Inno Setup package when using native desktop mode.

## Customer still needs on the PC

| Item | Notes |
|------|--------|
| **MySQL 8** | Local primary database (`smart_weighbridge`) |
| **`.env`** | MySQL password, COM port, cloud sync settings |
| **First-time setup** | Run `setup-station.ps1` once before first native launch |

NativePHP bundles PHP inside the `.exe` — customers do **not** need PHP installed when using the **built** native app.

## Inno Setup vs NativePHP

| Approach | Customer gets | Browser URL? |
|----------|---------------|--------------|
| **Inno Setup** (`SmartWeighbridge-Setup.exe`) | PHP app + launcher | Yes — opens browser at 127.0.0.1:8000 |
| **NativePHP** (`native:build win`) | Single native `.exe` | **No** — own app window |

We are moving to **NativePHP** for the true desktop experience.

## GitHub Release

Tag a version to build in CI (extend `.github/workflows/release.yml` with `native:build` when ready).

Current releases use Inno Setup; native builds will be added in a future release (v1.1.0+).
