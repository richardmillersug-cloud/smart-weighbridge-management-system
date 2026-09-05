# Native Windows desktop app (NativePHP)

Smart Weighbridge runs as a **native Windows desktop window** (no browser address bar) using [NativePHP](https://nativephp.com/).

**MySQL remains the local database** on the station PC — the native app connects via `.env` the same way as before.

## v1.1.2+ native installer fixes

- PHP binary is fully extracted **before** Electron packaging (fixes incomplete builds)
- Windows **`icudtl.dat` / ICU startup** fix — app sets working directory to install folder
- Installs to **`C:\Program Files\SmartWeighbridge`** (same as legacy Inno Setup)
- CI verifies `icudtl.dat` and `php.exe` before publishing

## Development (on the station/build PC)

```powershell
php artisan native:install
npm run build
php artisan native:run
```

Or double-click **`SmartWeighbridge.bat`** (calls `native:run`).

## Build production `.exe` for customers

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\build-native.ps1
```

Output: `nativephp/electron/dist/*-setup.exe` → renamed to **`SmartWeighbridge-Native.exe`** in GitHub Releases.

## Customer still needs on the PC

| Item | Notes |
|------|--------|
| **MySQL 8** | Local primary database (`smart_weighbridge`) |
| **`.env`** | MySQL password, COM port, cloud sync settings |
| **First-time setup** | See `extras/setup-station.ps1` after install |

NativePHP bundles PHP inside the `.exe` — customers do **not** need PHP installed.

## Inno Setup vs NativePHP

| Approach | Customer gets | Browser URL? |
|----------|---------------|--------------|
| **Inno Setup** (`SmartWeighbridge-Setup.exe`) | PHP app + launcher | Yes — opens browser at 127.0.0.1:8000 |
| **NativePHP** (`SmartWeighbridge-Native.exe`) | Native installer | **No** — own app window |

GitHub Releases ship **`SmartWeighbridge-Native.exe`** for v1.1.2+.
