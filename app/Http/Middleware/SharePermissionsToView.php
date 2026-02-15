<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SharePermissionsToView
{
    public function __construct(
        protected PermissionService $permissions
    ) {}

    /**
     * Share user permissions with all Blade views for sidebar visibility, etc.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $userPerms = $this->permissions->getAllUserPermissions($user->id);
            View::share('userPermissions', $userPerms);
            View::share('currentUser', $user);
            View::share('isSystemAdmin', $user->isSystemAdmin());
        }

        return $next($request);
    }
}
