<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an audit event.
     */
    public function log(string $action, ?string $details = null, array $context = []): void
    {
        $userId = $context['user_id'] ?? Auth::id();

        $data = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ];

        // Add structured details_raw if we have context
        if (!empty($context)) {
            $data['details_raw'] = json_encode($context, JSON_UNESCAPED_SLASHES);
        }

        try {
            AuditLog::create($data);
        } catch (\Throwable $e) {
            \Log::error('AuditService: Failed to write audit log', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log a user action (CRUD operations).
     */
    public function actionLog(string $module, string $actionType, string $status = 'success', array $meta = []): void
    {
        $details = json_encode(array_merge([
            'module' => $module,
            'action_type' => $actionType,
            'status' => $status,
        ], $meta), JSON_UNESCAPED_SLASHES);

        $this->log("ACTION-{$module}", $details, $meta);
    }
}
