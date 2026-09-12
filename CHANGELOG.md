# Changelog

All notable releases of Smart Weighbridge Management System.

## [1.2.0] - 2026-09-12

### Removed
- **Electron / NativePHP desktop runtime, permanently.** Chromium loaded ICU (`icudtl.dat`) before the window existed, so `Invalid file descriptor to ICU data received` could not be fixed from PHP or JavaScript. The `nativephp/desktop` dependency, `config/nativephp.php`, `NativeAppServiceProvider`, the `installer/native-electron` pack, and the native build scripts are gone.

### Changed
- Releases now ship **`SmartWeighbridge-Setup.exe`** (Inno Setup, no bundled Chromium) instead of the 200 MB `SmartWeighbridge-Native.exe`.
- The station window is opened by `installer/scripts/open-desktop-window.ps1` using the Edge/Chrome engine already on Windows — a desktop app window with no address bar.

### Added
- `installer/scripts/check-php-extensions.php` — the launcher now names the exact missing PHP extensions and the `php.ini` lines to add.
- `tests/Feature/DesktopLauncherTest.php` and `installer/scripts/verify-release-build.ps1` — the test suite and the release build both fail if an Electron runtime returns.

## [1.1.8] - 2026-09-12

### Fixed
- Desktop launch no longer starts Electron (`icudtl.dat` / ICU crash). `SmartWeighbridge.bat` starts PHP and opens a desktop app window via Windows Edge/Chrome `--app` mode. Native installer shortcuts pass `--icu-data-dir` at process start.

## [1.1.7] - 2026-09-12

### Fixed
- Native desktop window ICU crash (`Invalid file descriptor to ICU data received`) — ship `icudtl.dat` next to the exe and start with the install folder as the working directory. First-run still uses PHP on PATH.

## [1.1.6] - 2026-09-05

### Fixed
- Native release packaging — keep stock NativePHP electron-builder so CI publishes `SmartWeighbridge-Native.exe`. Runtime still uses PHP on PATH and the first-run setup wizard.

## [1.1.5] - 2026-09-05

### Changed
- **First-run station wizard** — customers install PHP 8.4+ and MySQL 8, then the app creates the database, runs migrations, and collects COM port plus optional cloud sync

### Fixed
- Release CI — remove blocking verify step so native installer publishes (ICU/build patches from v1.1.2 remain)

## [1.1.4] - 2026-09-05

### Fixed
- Release CI verify script PowerShell null handling and allow NSIS-only dist output

## [1.1.3] - 2026-09-05

### Fixed
- Release CI verify step — search all native dist paths, accept `win-x64-unpacked`, and re-apply electron patches after `npm install`

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
