<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $this->authorize('settings.manage');

        return view('settings.edit', ['settings' => Setting::allCached()]);
    }

    public function update(UpdateSettingsRequest $request, AuditService $audit): RedirectResponse
    {
        $old = Setting::allCached();

        foreach ($request->validated() as $key => $value) {
            Setting::set($key, $value !== null ? (string) $value : null);
        }

        $audit->log('UPDATE', 'settings', null, $old, $request->validated());

        return redirect()
            ->route('settings.edit')
            ->with('success', 'System settings saved.');
    }
}
