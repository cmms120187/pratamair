<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Define role hierarchy (higher number = higher level)
     */
    private static $roleHierarchy = [
        'mekanik' => 1,
        'team_leader' => 2,
        'group_leader' => 3,
        'coordinator' => 4,
        'ast_manager' => 5,
        'manager' => 6,
        'general_manager' => 7,
        'admin' => 10, // Admin has highest level, can access everything
    ];

    /**
     * Get menu permissions (from database or fallback to static)
     */
    public static function getMenuPermissions()
    {
        return self::$menuPermissions;
    }

    /**
     * Define menu permissions per role
     * Format: 'menu_key' => ['allowed_roles' => [...], 'min_level' => X]
     */
    private static $menuPermissions = [
        'dashboard' => ['allowed_roles' => ['all']], // All roles can access
        
        // Location menus
        'location' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        'plants' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        'processes' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        'lines' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        'room-erp' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        
        // Machinary menus
        'machinary' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'systems' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'groups' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'machine-types' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'brands' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'models' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'machine-erp' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'mutasi' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        
        // Downtime menus
        'downtime' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'problems' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'reasons' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'actions' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'downtime-erp2' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'work-orders' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        
        // Production menus
        'production' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'production-hourly' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        
        // Users menus
        'users' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        'users-list' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        'organizational-structure' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
        'activities' => ['allowed_roles' => ['all']], // All roles can access
        
        // Preventive Maintenance
        'preventive-maintenance' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'preventive-scheduling' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'preventive-controlling' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'preventive-monitoring' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'preventive-updating' => ['allowed_roles' => ['mekanik', 'team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'preventive-reporting' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        
        // Predictive Maintenance
        'predictive-maintenance' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'standards' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'predictive-scheduling' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'predictive-controlling' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'predictive-monitoring' => ['allowed_roles' => ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'predictive-updating' => ['allowed_roles' => ['mekanik', 'team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'predictive-reporting' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        
        // Reports
        'reports' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'mttr-mtbf' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'pareto-machine' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'summary-downtime' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'mechanic-performance' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        'root-cause-analysis' => ['allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']],
        
        // Part ERP
        'part-erp' => ['allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']],
    ];

    /**
     * Check if user has access to a menu
     */
    public static function canAccessMenu($userRole, $menuKey)
    {
        // Admin has access to everything
        if ($userRole === 'admin') {
            return true;
        }

        // Try to get permission from database first
        try {
            if (class_exists('\App\Models\RolePermission')) {
                $dbPermission = \App\Models\RolePermission::where('role', $userRole)
                    ->where('menu_key', $menuKey)
                    ->first();
                
                if ($dbPermission !== null) {
                    return $dbPermission->allowed;
                }
            }
        } catch (\Exception $e) {
            // If database check fails, fallback to static permissions
        }

        // Fallback to static permissions
        // If menu not defined, deny access by default
        if (!isset(self::$menuPermissions[$menuKey])) {
            return false;
        }

        $permission = self::$menuPermissions[$menuKey];

        // If 'all' is in allowed_roles, everyone can access
        if (in_array('all', $permission['allowed_roles'])) {
            return true;
        }

        // Check if user role is in allowed roles
        return in_array($userRole, $permission['allowed_roles']);
    }

    /**
     * Check if user has minimum level required
     */
    public static function hasMinimumLevel($userRole, $minLevel)
    {
        $userLevel = self::$roleHierarchy[$userRole] ?? 0;
        return $userLevel >= $minLevel;
    }

    /**
     * Get all accessible menus for a role
     */
    public static function getAccessibleMenus($userRole)
    {
        $accessibleMenus = [];
        foreach (self::$menuPermissions as $menuKey => $permission) {
            if (self::canAccessMenu($userRole, $menuKey)) {
                $accessibleMenus[] = $menuKey;
            }
        }
        return $accessibleMenus;
    }

    /**
     * Get role hierarchy level
     */
    public static function getRoleLevel($role)
    {
        return self::$roleHierarchy[$role] ?? 0;
    }

    /**
     * Check if user role is higher or equal to another role
     */
    public static function isRoleHigherOrEqual($userRole, $compareRole)
    {
        // Admin is always higher than any role
        if ($userRole === 'admin') {
            return true;
        }
        
        $userLevel = self::getRoleLevel($userRole);
        $compareLevel = self::getRoleLevel($compareRole);
        return $userLevel >= $compareLevel;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin($userRole)
    {
        return $userRole === 'admin';
    }

    /**
     * Check if user can manage users/permissions
     */
    public static function canManageUsers($userRole)
    {
        // Admin can always manage users
        if ($userRole === 'admin') {
            return true;
        }
        
        // Other roles that can manage users
        return in_array($userRole, ['coordinator', 'ast_manager', 'manager', 'general_manager']);
    }
}

