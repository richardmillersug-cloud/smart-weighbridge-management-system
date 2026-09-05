# Native Windows desktop app (NativePHP)

Smart Weighbridge runs as a **native Windows desktop window** using [NativePHP](https://nativephp.com/).

The station PC must have **PHP 8.4+ on PATH** and **MySQL 8**. First launch opens the in-app setup wizard (database, COM port, optional cloud sync).

## Development

```powershell
php artisan native:install
npm run build
php artisan native:run
```

Or double-click **`SmartWeighbridge.bat`**.

## Production build

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\build-native.ps1
```

Output is renamed to **`SmartWeighbridge-Native.exe`** on GitHub Releases.

## Customer still needs on the PC

| Item | Notes |
|------|--------|
| **PHP 8.4+** | On PATH (`php -v`). The app prefers this over any bundled binary. |
| **MySQL 8** | Local primary database — created by the first-run wizard |
| **First launch** | Setup screen in the app — not PowerShell, not a `.env` edit |

## Inno Setup vs NativePHP

| Approach | Customer gets | Window |
|----------|---------------|--------|
| **Inno Setup** (`SmartWeighbridge-Setup.exe`) | App + launcher | Browser or native, depending on build |
| **NativePHP** (`SmartWeighbridge-Native.exe`) | Desktop installer | Own app window |
