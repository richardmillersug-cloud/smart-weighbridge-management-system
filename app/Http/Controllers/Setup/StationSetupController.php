<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Services\Station\StationSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StationSetupController extends Controller
{
    public function show(StationSetupService $setup): View
    {
        $setup->prepareEnvironment();

        return view('setup.show', [
            'checks' => $setup->prerequisites(),
        ]);
    }

    public function store(Request $request, StationSetupService $setup): RedirectResponse
    {
        $validated = $request->validate([
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['required', 'string'],
            'com_port' => ['required', 'string', 'max:32'],
            'cloud_sync_enabled' => ['sometimes', 'boolean'],
            'db_cloud_host' => ['required_if:cloud_sync_enabled,1', 'nullable', 'string', 'max:255'],
            'db_cloud_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'db_cloud_database' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]*$/'],
            'db_cloud_username' => ['required_if:cloud_sync_enabled,1', 'nullable', 'string', 'max:255'],
            'db_cloud_password' => ['required_if:cloud_sync_enabled,1', 'nullable', 'string'],
            'db_cloud_ssl_ca' => ['nullable', 'string', 'max:512'],
            'cloud_ssl_ca' => ['nullable', 'file', 'max:2048'],
        ], [
            'db_password.required' => 'Enter the MySQL password for this PC.',
            'db_cloud_host.required_if' => 'Enter the cloud database host, or turn cloud sync off.',
            'db_cloud_username.required_if' => 'Enter the cloud database username, or turn cloud sync off.',
            'db_cloud_password.required_if' => 'Enter the cloud database password, or turn cloud sync off.',
        ]);

        try {
            $result = $setup->install($validated, $request->file('cloud_ssl_ca'));
        } catch (Throwable $e) {
            return back()
                ->withInput($request->except(['db_password', 'db_cloud_password']))
                ->withErrors(['db_password' => 'Setup could not finish: '.$e->getMessage()]);
        }

        $redirect = redirect()
            ->route('login')
            ->with('status', 'Station is ready. Sign in with admin@example.com / password and change it immediately.');

        if ($result['warnings'] !== []) {
            $redirect->with('warning', implode(' ', $result['warnings']));
        }

        return $redirect;
    }
}
