<?php

namespace App\Providers;

use App\Services\CloudSync\CloudSyncDispatcher;
use Illuminate\Support\ServiceProvider;

class CloudSyncServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('cloud_sync.enabled')) {
            return;
        }

        CloudSyncDispatcher::registerModelHooks();
    }
}
