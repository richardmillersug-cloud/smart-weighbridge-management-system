<?php

namespace App\Services;

use App\Enums\CashSessionStatus;
use App\Models\CashSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashSessionService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Return the operator's open cash session, creating one automatically if needed.
     */
    public function ensureOpen(float $openingCash = 0): CashSession
    {
        $existing = CashSession::query()
            ->open()
            ->where('user_id', Auth::id())
            ->latest('opened_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        $session = CashSession::create([
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'status' => CashSessionStatus::Open,
        ]);

        $this->audit->log('OPEN_CASH_SESSION', 'cash_sessions', $session->id, null, [
            'opening_cash' => $openingCash,
            'auto' => true,
        ]);

        return $session;
    }

    public function close(CashSession $session, float $actualCash, ?string $notes = null): CashSession
    {
        if ($session->status !== CashSessionStatus::Open) {
            throw ValidationException::withMessages([
                'session' => 'This cash session is already closed.',
            ]);
        }

        return DB::transaction(function () use ($session, $actualCash, $notes): CashSession {
            $expected = (float) $session->opening_cash + $session->cashCollected();

            $session->forceFill([
                'closed_at' => now(),
                'expected_cash' => $expected,
                'actual_cash' => $actualCash,
                'difference' => round($actualCash - $expected, 2),
                'closing_notes' => $notes,
                'status' => CashSessionStatus::Closed,
            ])->save();

            $this->audit->log('CLOSE_CASH_SESSION', 'cash_sessions', $session->id, null, [
                'expected_cash' => $expected,
                'actual_cash' => $actualCash,
                'difference' => $session->difference,
            ]);

            return $session;
        });
    }
}
