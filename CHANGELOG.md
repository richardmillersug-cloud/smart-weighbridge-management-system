# Changelog

All notable releases of Smart Weighbridge Management System.

## [1.1.2] - 2026-09-05

### Fixed
- **Native Windows installer ICU crash** — await PHP binary extract before packaging, set Electron working directory on Windows, unpack `icudtl.dat`, install to `Program Files\SmartWeighbridge`
- Native build CI now verifies `icudtl.dat` and `php.exe` exist before release

## [1.1.1] - 2026-09-05

### Changed
- **Customer setup guide** — native v1.1.0 install steps, ICU error troubleshooting, v1.0.1 fallback

### Fixed
- Native release CI build (`.env` prep, electron lock sync, artifact path)

## [1.1.0] - 2026-09-02

### Added
- **Native Windows desktop app** via NativePHP — own window, no browser address bar
- `php artisan native:run` and `SmartWeighbridge.bat` launch native mode
- Auto-started queue worker for cloud sync in native mode
- `installer/scripts/build-native.ps1` and `installer/NATIVE-DESKTOP.md`

### Changed
- GitHub Releases now ship the **NativePHP-built `.exe`** (primary download)
- Inno Setup package remains available for legacy browser-based install

## [1.0.1] - 2026-09-02

### Added
- Custom app icon for desktop shortcut and installer (`installer/assets/app-icon.ico`)

## [1.0.0] - 2026-09-02

### Added
- XK3190-DS17 live weighing on Windows COM port
- Local MySQL primary database with DigitalOcean cloud sync
- Admin Cloud Sync page (status, full sync, retry failed)
- Windows installer (`SmartWeighbridge-Setup.exe`) and launcher scripts
- Customer setup guide (`CUSTOMER-SETUP.md`)

### Fixed
- DS17 low-weight parsing and live display cache issues

[1.1.2]: https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/tag/v1.1.2
[1.1.1]: https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/tag/v1.1.1
[1.1.0]: https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/tag/v1.1.0
[1.0.1]: https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/tag/v1.0.1
[1.0.0]: https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/tag/v1.0.0
