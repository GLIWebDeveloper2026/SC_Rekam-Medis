<?php

namespace App\Http\Middleware;

use App\Services\AuditTrail;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->hasPermission($permission)) {
            $this->auditTrail->record(
                action: 'authorization.denied',
                resourceType: 'route',
                resourceId: $request->route()?->getName(),
                result: 'denied',
                user: $request->user(),
                reason: 'Missing permission: '.$permission,
            );

            abort(403);
        }

        return $next($request);
    }
}
