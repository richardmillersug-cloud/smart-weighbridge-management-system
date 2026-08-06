<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Write an entry to the audit trail.
     */
    public function log(
        string $action,
        string $module,
        int|string|null $recordId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'old_value' => $oldValue !== null ? $this->scrub($oldValue) : null,
            'new_value' => $newValue !== null ? $this->scrub($newValue) : null,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Remove sensitive attributes before persisting to the log.
     */
    private function scrub(array $values): array
    {
        unset($values['password'], $values['remember_token']);

        return $values;
    }
}
