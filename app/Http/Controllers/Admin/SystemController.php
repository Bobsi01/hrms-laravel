<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index()
    {
        // Database stats
        $dbSize = DB::selectOne("SELECT pg_size_pretty(pg_database_size(current_database())) as size")->size ?? 'N/A';

        $connections = DB::selectOne("
            SELECT
                (SELECT count(*) FROM pg_stat_activity WHERE state = 'active') as active,
                (SELECT count(*) FROM pg_stat_activity WHERE state = 'idle') as idle,
                (SELECT setting::int FROM pg_settings WHERE name = 'max_connections') as max
        ");

        $cacheHit = DB::selectOne("
            SELECT ROUND(100.0 * blks_hit / NULLIF(blks_hit + blks_read, 0), 1) as ratio
            FROM pg_stat_database WHERE datname = current_database()
        ");

        // Active users (within 15 mins)
        $activeUsers = DB::table('audit_logs')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->distinct('user_id')
            ->count('user_id');

        // System logs
        $logsWeek = DB::table('system_logs')->where('created_at', '>=', now()->subWeek())->count();

        // Recent activity from audit_logs
        $recentLogs = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select('audit_logs.*', 'users.full_name as user_name')
            ->orderByDesc('audit_logs.created_at')
            ->limit(10)
            ->get();

        // Consolidated stats for the view
        $stats = [
            'db_size' => $dbSize,
            'active_connections' => $connections->active ?? 0,
            'idle_connections' => $connections->idle ?? 0,
            'max_connections' => $connections->max ?? 0,
            'cache_hit_ratio' => $cacheHit->ratio ?? 0,
            'active_users' => $activeUsers,
            'php_version' => PHP_VERSION,
            'logs_week' => $logsWeek,
        ];

        return view('admin.system.index', compact('stats', 'recentLogs'));
    }
}
