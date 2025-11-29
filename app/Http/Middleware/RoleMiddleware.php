<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\PermissionHelper;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role ?? 'mekanik';

        // Admin can access everything, bypass role check
        if ($userRole === 'admin') {
            return $next($request);
        }

        // First, check permission from database based on route name
        $routeName = $request->route()->getName();
        if ($routeName) {
            // Map route names to menu keys
            $menuKey = $this->getMenuKeyFromRoute($routeName);
            if ($menuKey) {
                // Check permission from database
                if (PermissionHelper::canAccessMenu($userRole, $menuKey)) {
                    return $next($request);
                } else {
                    // Permission denied from database
                    abort(403, 'Unauthorized access. You do not have permission to access this page.');
                }
            }
        }

        // Fallback: Check if user role is in allowed roles (for backward compatibility)
        // Only if route is not mapped to a menu key
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized access. You do not have permission to access this page.');
        }

        return $next($request);
    }

    /**
     * Map route name to menu key
     */
    private function getMenuKeyFromRoute($routeName)
    {
        $routeMenuMap = [
            'room-erp.index' => 'room-erp',
            'room-erp.create' => 'room-erp',
            'room-erp.store' => 'room-erp',
            'room-erp.show' => 'room-erp',
            'room-erp.edit' => 'room-erp',
            'room-erp.update' => 'room-erp',
            'room-erp.destroy' => 'room-erp',
            'room-erp.upload' => 'room-erp',
            'room-erp.download' => 'room-erp',
            'plants.index' => 'plants',
            'plants.create' => 'plants',
            'plants.store' => 'plants',
            'plants.show' => 'plants',
            'plants.edit' => 'plants',
            'plants.update' => 'plants',
            'plants.destroy' => 'plants',
            'processes.index' => 'processes',
            'processes.create' => 'processes',
            'processes.store' => 'processes',
            'processes.show' => 'processes',
            'processes.edit' => 'processes',
            'processes.update' => 'processes',
            'processes.destroy' => 'processes',
            'lines.index' => 'lines',
            'lines.create' => 'lines',
            'lines.store' => 'lines',
            'lines.show' => 'lines',
            'lines.edit' => 'lines',
            'lines.update' => 'lines',
            'lines.destroy' => 'lines',
            'machine-erp.index' => 'machine-erp',
            'machine-erp.create' => 'machine-erp',
            'machine-erp.store' => 'machine-erp',
            'machine-erp.show' => 'machine-erp',
            'machine-erp.edit' => 'machine-erp',
            'machine-erp.update' => 'machine-erp',
            'machine-erp.destroy' => 'machine-erp',
            'standards.index' => 'standards',
            'standards.create' => 'standards',
            'standards.store' => 'standards',
            'standards.show' => 'standards',
            'standards.edit' => 'standards',
            'standards.update' => 'standards',
            'standards.destroy' => 'standards',
            'users.index' => 'users-list',
            'users.create' => 'users-list',
            'users.store' => 'users-list',
            'users.show' => 'users-list',
            'users.edit' => 'users-list',
            'users.update' => 'users-list',
            'users.destroy' => 'users-list',
            'work-orders.index' => 'work-orders',
            'work-orders.create' => 'work-orders',
            'work-orders.store' => 'work-orders',
            'work-orders.show' => 'work-orders',
            'work-orders.edit' => 'work-orders',
            'work-orders.update' => 'work-orders',
            'work-orders.destroy' => 'work-orders',
            'problems.index' => 'problems',
            'problems.create' => 'problems',
            'problems.store' => 'problems',
            'problems.show' => 'problems',
            'problems.edit' => 'problems',
            'problems.update' => 'problems',
            'problems.destroy' => 'problems',
            'reasons.index' => 'reasons',
            'reasons.create' => 'reasons',
            'reasons.store' => 'reasons',
            'reasons.show' => 'reasons',
            'reasons.edit' => 'reasons',
            'reasons.update' => 'reasons',
            'reasons.destroy' => 'reasons',
            'actions.index' => 'actions',
            'actions.create' => 'actions',
            'actions.store' => 'actions',
            'actions.show' => 'actions',
            'actions.edit' => 'actions',
            'actions.update' => 'actions',
            'actions.destroy' => 'actions',
            'downtime-erp2.index' => 'downtime-erp2',
            'downtime-erp2.create' => 'downtime-erp2',
            'downtime-erp2.store' => 'downtime-erp2',
            'downtime-erp2.show' => 'downtime-erp2',
            'downtime-erp2.edit' => 'downtime-erp2',
            'downtime-erp2.update' => 'downtime-erp2',
            'downtime-erp2.destroy' => 'downtime-erp2',
            'activities.index' => 'activities',
            'activities.create' => 'activities',
            'activities.store' => 'activities',
            'activities.show' => 'activities',
            'activities.edit' => 'activities',
            'activities.update' => 'activities',
            'activities.destroy' => 'activities',
            'preventive-maintenance.scheduling.index' => 'preventive-scheduling',
            'preventive-maintenance.controlling.index' => 'preventive-controlling',
            'preventive-maintenance.monitoring.index' => 'preventive-monitoring',
            'preventive-maintenance.updating.index' => 'preventive-updating',
            'preventive-maintenance.reporting.index' => 'preventive-reporting',
            'predictive-maintenance.scheduling.index' => 'predictive-scheduling',
            'predictive-maintenance.controlling.index' => 'predictive-controlling',
            'predictive-maintenance.monitoring.index' => 'predictive-monitoring',
            'predictive-maintenance.updating.index' => 'predictive-updating',
            'predictive-maintenance.reporting.index' => 'predictive-reporting',
            'part-erp.index' => 'part-erp',
            'part-erp.create' => 'part-erp',
            'part-erp.store' => 'part-erp',
            'part-erp.show' => 'part-erp',
            'part-erp.edit' => 'part-erp',
            'part-erp.update' => 'part-erp',
            'part-erp.destroy' => 'part-erp',
        ];

        return $routeMenuMap[$routeName] ?? null;
    }
}

