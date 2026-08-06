<?php

namespace App\Providers;

use App\Models\WeighbridgeStation;
use App\Services\Weighbridge\DummyWeightReaderService;
use App\Services\Weighbridge\SerialWeightReaderService;
use App\Services\Weighbridge\WeightReaderInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WeightReaderInterface::class, function ($app): WeightReaderInterface {
            return match (config('weighbridge.driver')) {
                'serial', 'xk3190' => SerialWeightReaderService::fromStation(
                    WeighbridgeStation::defaultStation()
                ),
                default => new DummyWeightReaderService(),
            };
        });
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.industrial');
        Paginator::defaultSimpleView('vendor.pagination.industrial');

        // System Administrators implicitly pass every permission check.
        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole('System Administrator') ? true : null;
        });
    }
}
