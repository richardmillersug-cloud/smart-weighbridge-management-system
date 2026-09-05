<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StationSetupState
{
    public static function markerPath(): string
    {
        return storage_path('app/station-setup.complete');
    }

    public static function isComplete(): bool
    {
        if (filter_var(config('station.setup_complete'), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (app()->environment('testing')) {
            return false;
        }

        if (File::exists(self::markerPath())) {
            return true;
        }

        return self::detectExistingInstall();
    }

    public static function markComplete(): void
    {
        File::ensureDirectoryExists(dirname(self::markerPath()));
        File::put(self::markerPath(), now()->toIso8601String().PHP_EOL);
        config(['station.setup_complete' => true]);
    }

    private static function detectExistingInstall(): bool
    {
        try {
            if (! Schema::hasTable('users')) {
                return false;
            }

            if (DB::table('users')->exists()) {
                self::markComplete();

                return true;
            }
        } catch (Throwable) {
            return false;
        }

        return false;
    }
}
