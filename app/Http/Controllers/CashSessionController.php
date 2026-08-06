<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Services\CashSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashSessionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CashSession::class);

        $sessions = CashSession::with('user')
            ->latest('opened_at')
            ->paginate(20);

        $openSession = CashSession::query()
            ->open()
            ->where('user_id', auth()->id())
            ->first();

        return view('cash-sessions.index', compact('sessions', 'openSession'));
    }

    public function close(Request $request, CashSession $cashSession, CashSessionService $service): RedirectResponse
    {
        $this->authorize('close', $cashSession);

        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->close($cashSession, (float) $data['actual_cash'], $data['closing_notes'] ?? null);

        return redirect()->route('cash-sessions.index')->with('success', 'Cash session closed.');
    }

    public function show(CashSession $cashSession): View
    {
        $this->authorize('view', $cashSession);

        $cashSession->load(['user', 'payments.invoice']);

        return view('cash-sessions.show', compact('cashSession'));
    }
}
