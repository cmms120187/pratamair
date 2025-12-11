<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

class DataFilterHelper
{
    /**
     * Filter query based on user role
     * For mekanik: only show data related to their NIK
     * For other roles: show all data
     */
    public static function filterByUserRole(Builder $query, $user, $nikColumn = 'id_mekanik')
    {
        // If user is mekanik, filter by their NIK
        if ($user && $user->role === 'mekanik' && $user->nik) {
            $query->where($nikColumn, $user->nik);
        }
        
        return $query;
    }

    /**
     * Check if current route should be excluded from filtering
     * (Dashboard and Reports should not be filtered)
     */
    public static function shouldFilterRoute($routeName)
    {
        $excludedRoutes = [
            'dashboard',
            'mttr-mtbf.index',
            'pareto-machine.index',
            'summary-downtime.index',
            'mechanic-performance.index',
            'root-cause-analysis.index',
            'reports.index',
        ];

        // Check if route name contains any excluded pattern
        foreach ($excludedRoutes as $excluded) {
            if (str_contains($routeName, $excluded)) {
                return false;
            }
        }

        return true;
    }
}

