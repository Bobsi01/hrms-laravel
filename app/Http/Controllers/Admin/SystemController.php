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
        $logStats = [
            'total' => DB::table('system_logs')->count(),
            'last24h' => DB::table('system_logs')->where('created_at', '>=', now()->subDay())->count(),
            'errors' => DB::table('system_logs')
                ->where('created_at', '>=', now()->subDay())
                ->where('log_code', 'ilike', '%ERR%')
                ->count(),
        ];

        $recentLogs = DB::table('system_logs')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // PHP info
        $phpInfo = [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => config('app.timezone'),
        ];

        return view('admin.system.index', compact(
            'dbSize', 'connections', 'cacheHit', 'activeUsers',
            'logStats', 'recentLogs', 'phpInfo'
        ));
    }
}
