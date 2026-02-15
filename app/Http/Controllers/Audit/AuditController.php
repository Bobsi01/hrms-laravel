<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct(
        protected PermissionService $permissions
    ) {}

    /**
     * Audit trail viewer.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('action', 'ilike', "%{$escaped}%")
                  ->orWhere('details', 'ilike', "%{$escaped}%");
            });
        }

        if ($action = $request->input('action')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $action);
            $query->where('action', 'ilike', "%{$escaped}%");
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->paginate(50);

        return view('audit.index', compact('auditLogs'));
    }
}
