<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The station window must never be an Electron process again. Electron loaded
 * Chromium ICU before the window existed and crashed with
 * "Invalid file descriptor to ICU data received" on customer PCs.
 */
class DesktopLauncherTest extends TestCase
{
    public function test_electron_desktop_runtime_is_not_a_dependency(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $packages = array_keys(array_merge(
            $composer['require'] ?? [],
            $composer['require-dev'] ?? []
        ));

        foreach ($packages as $package) {
            $this->assertStringNotContainsString(
                'nativephp',
                $package,
                'Electron desktop runtime must stay out of composer.json.'
            );
        }
    }

    public function test_electron_files_are_removed(): void
    {
        foreach ([
            'config/nativephp.php',
            'app/Providers/NativeAppServiceProvider.php',
            'installer/native-electron',
        ] as $path) {
            $this->assertFileDoesNotExist(base_path($path));
        }
    }

    public function test_launcher_opens_a_desktop_window_without_electron(): void
    {
        $launcher = file_get_contents(base_path('SmartWeighbridge.bat'));

        $this->assertStringNotContainsString('native:run', $launcher);
        $this->assertStringContainsString('artisan serve', $launcher);
        $this->assertStringContainsString('open-desktop-window.ps1', $launcher);
        $this->assertStringContainsString('check-php-extensions.php', $launcher);
    }
}
