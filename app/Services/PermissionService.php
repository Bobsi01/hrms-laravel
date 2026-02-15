<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employee;
use App\Models\PositionAccessPermission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    /**
     * Access level hierarchy (higher includes lower).
     */
    protected const LEVEL_HIERARCHY = [
        'none' => 0,
        'read' => 1,
        'write' => 2,
        'manage' => 3,
    ];

    /**
     * Get the position ID for a user.
     */
    public function getUserPositionId(int $userId): ?int
    {
        return Cache::remember("user_position:{$userId}", 300, function () use ($userId) {
            return Employee::where('user_id', $userId)
                ->where('status', 'active')
                ->value('position_id');
        });
    }

    /**
     * Get effective access level for a user on a specific resource.
     *
     * @return string 'none'|'read'|'write'|'manage'
     */
    public function getEffectiveAccess(int $userId, string $domain, string $resourceKey): string
    {
        $cacheKey = "perms:{$userId}:{$domain}:{$resourceKey}";

        return Cache::remember($cacheKey, 300, function () use ($userId, $domain, $resourceKey) {
            // System admin bypass
            $user = User::find($userId);
            if (!$user) {
                return 'none';
            }

            if ($user->isSystemAdmin()) {
                return 'manage';
            }

            // Self-service check — uses PermissionCatalog as single source of truth
            if (PermissionCatalog::isSelfService($domain, $resourceKey)) {
                return 'read';
            }

            // Get user's position
            $positionId = $this->getUserPositionId($userId);
            if (!$positionId) {
                return 'none';
            }

            // Look up permission
            $level = PositionAccessPermission::where('position_id', $positionId)
                ->where('domain', $domain)
                ->where('resource_key', $resourceKey)
                ->value('access_level');

            return $level ?: 'none';
        });
    }

    /**
     * Check if a user has at least the specified access level.
     */
    public function userHasAccess(int $userId, string $domain, string $resourceKey, string $requiredLevel = 'read'): bool
    {
        $effective = $this->getEffectiveAccess($userId, $domain, $resourceKey);

        return (self::LEVEL_HIERARCHY[$effective] ?? 0) >= (self::LEVEL_HIERARCHY[$requiredLevel] ?? 0);
    }

    /**
     * Check if the currently authenticated user has access.
     */
    public function userCan(string $domain, string $resourceKey, string $requiredLevel = 'read'): bool
    {
        $userId = auth()->id();
        if (!$userId) {
            return false;
        }

        return $this->userHasAccess($userId, $domain, $resourceKey, $requiredLevel);
    }

    /**
     * Clear all cached permissions for a user.
     */
    public function clearUserCache(int $userId): void
    {
        Cache::forget("user_position:{$userId}");

        // Clear all permission cache keys for this user
        $catalog = PermissionCatalog::all();
        foreach ($catalog as $domain => $domainData) {
            foreach (array_keys($domainData['resources'] ?? []) as $resourceKey) {
                Cache::forget("perms:{$userId}:{$domain}:{$resourceKey}");
            }
        }
    }

    /**
     * Get all permissions for a user (for sidebar visibility, etc.).
     */
    public function getAllUserPermissions(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        if ($user->isSystemAdmin()) {
            // Return 'manage' for everything
            return ['__system_admin' => true];
        }

        $positionId = $this->getUserPositionId($userId);
        $permissions = [];

        // Always include self-service resources
        foreach (PermissionCatalog::all() as $domain => $domainData) {
            foreach ($domainData['resources'] ?? [] as $key => $resource) {
                if (!empty($resource['self_service'])) {
                    $permissions["{$domain}.{$key}"] = 'read';
                }
            }
        }

        // Merge position-based permissions (higher level wins)
        if ($positionId) {
            $posPerms = PositionAccessPermission::where('position_id', $positionId)
                ->get()
                ->each(function ($perm) use (&$permissions) {
                    $key = "{$perm->domain}.{$perm->resource_key}";
                    $existing = self::LEVEL_HIERARCHY[$permissions[$key] ?? 'none'] ?? 0;
                    $incoming = self::LEVEL_HIERARCHY[$perm->access_level] ?? 0;
                    if ($incoming > $existing) {
                        $permissions[$key] = $perm->access_level;
                    }
                });
        }

        return $permissions;
    }
}
