<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function __construct(
        protected PermissionService $permissions
    ) {}

    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('module.access:hr_core,employees,read')
     */
    public function handle(Request $request, Closure $next, string $domain, string $resource, string $level = 'read'): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$this->permissions->userHasAccess($user->id, $domain, $resource, $level)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return redirect()->route('unauthorized')
                ->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
