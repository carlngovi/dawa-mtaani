<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Web\RoleRedirectController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforcePortalRole
 *
 * Maps each URL prefix to its allowed roles. If the authenticated user's
 * role does not match the portal they are trying to access, they are
 * silently redirected to their own correct portal.
 *
 * This runs AFTER the 'auth' middleware — only authenticated users reach it.
 */
class EnforcePortalRole
{
    /**
     * Portal prefix → allowed roles.
     * A user must have AT LEAST ONE of the listed roles to access that prefix.
     */
    private const PORTAL_ROLES = [
        'admin'     => ['network_admin', 'admin', 'super_admin', 'technical_admin',
                        'assistant_admin', 'admin_support', 'shared_accountant', 'system'],
        'super'     => ['super_admin', 'technical_admin'],
        'tech'      => ['technical_admin'],
        'finance'   => ['shared_accountant', 'admin', 'network_admin', 'super_admin'],
        'support'   => ['admin_support', 'admin', 'network_admin', 'super_admin'],
        'assistant' => ['assistant_admin', 'admin', 'network_admin', 'super_admin'],
        'wholesale' => ['wholesale_facility'],
        'logistics' => ['logistics_facility'],
        'retail'    => ['retail_facility'],
        'group'     => ['group_owner'],
        'field'     => ['network_field_agent'],
        'rep'       => ['sales_rep'],
        'sales'     => ['sales_rep'],
        'county'    => ['county_coordinator'],
        'store'        => ['customer', 'retail_facility', 'group_owner'],
        'manufacturer' => ['manufacturer'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        // Determine which portal prefix this request belongs to
        $prefix = $this->resolvePrefix($request);

        if ($prefix === null) {
            // Not a portal route (e.g. /dashboard, /profile) — allow through
            return $next($request);
        }

        $allowedRoles = self::PORTAL_ROLES[$prefix] ?? [];

        if (empty($allowedRoles)) {
            return $next($request);
        }

        if ($user->hasAnyRole($allowedRoles)) {
            return $next($request);
        }

        // User is authenticated but doesn't have access to this portal —
        // redirect them to their correct portal without exposing the 403
        return RoleRedirectController::redirectForUser($user);
    }

    private function resolvePrefix(Request $request): ?string
    {
        $segments = $request->segments();

        if (empty($segments)) {
            return null;
        }

        $first = $segments[0];

        return array_key_exists($first, self::PORTAL_ROLES) ? $first : null;
    }
}
