<?php

namespace App\Http\Controllers;

use App\Services\CloudSync\CloudSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CloudSyncController extends Controller
{
    public function index(CloudSyncService $cloudSync): View
    {
        $this->authorize('cloud-sync.manage');

        return view('cloud-sync.index', [
            'status' => $cloudSync->status(),
        ]);
    }

    public function syncFull(CloudSyncService $cloudSync): RedirectResponse
    {
        $this->authorize('cloud-sync.manage');

        if (! config('cloud_sync.enabled')) {
            return back()->with('error', 'Cloud sync is disabled. Set CLOUD_SYNC_ENABLED=true in .env.');
        }

        if (! CloudSyncService::cloudReachable()) {
            return back()->with('error', 'Cloud database is not reachable. Check connection settings and DigitalOcean trusted sources.');
        }

        set_time_limit(0);

        $summary = $cloudSync->syncAll();

        return back()->with(
            'success',
            sprintf('Full cloud sync finished. Synced %d record(s), %d failed.', $summary['synced'], $summary['failed']),
        );
    }

    public function syncRetry(CloudSyncService $cloudSync): RedirectResponse
    {
        $this->authorize('cloud-sync.manage');

        if (! config('cloud_sync.enabled')) {
            return back()->with('error', 'Cloud sync is disabled.');
        }

        if (! CloudSyncService::cloudReachable()) {
            return back()->with('error', 'Cloud database is not reachable.');
        }

        $retried = $cloudSync->retryFailed();

        return back()->with('success', sprintf('Retried %d failed sync record(s).', $retried));
    }
}
