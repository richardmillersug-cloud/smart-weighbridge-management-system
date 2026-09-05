<?php

namespace App\Providers;

use App\Support\StationSetupState;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     */
    public function boot(): void
    {
        Window::open()
            ->title('Smart Weighbridge')
            ->route(StationSetupState::isComplete() ? 'dashboard' : 'setup.show')
            ->width(1440)
            ->height(900)
            ->minWidth(1024)
            ->minHeight(700)
            ->rememberState()
            ->hideMenu()
            ->backgroundColor('#0f172a');
    }

    /**
     * PHP settings for the NativePHP runtime (MySQL, COM port, cloud sync).
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'max_execution_time' => '0',
            'max_input_time' => '0',
            'default_socket_timeout' => '120',
        ];
    }
}
