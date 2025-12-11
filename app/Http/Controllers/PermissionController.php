<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RolePermission;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display the permission management page
     */
    public function index()
    {
        try {
            // Get all roles
            $roles = ['mekanik', 'team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager', 'admin'];
            
            // Get menu keys from navigation (to ensure we only show menus that exist in navigation)
            $menuKeys = $this->getMenuKeysFromNavigation();
            
            // Get existing permissions from database
            $permissions = RolePermission::all()->keyBy(function ($item) {
                return $item->role . '_' . $item->menu_key;
            });
            
            // Get menu labels (for display)
            $menuLabels = $this->getMenuLabels();
            
            return view('permissions.index', compact('roles', 'menuKeys', 'permissions', 'menuLabels'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Error loading permissions: ' . $e->getMessage());
        }
    }
    
    /**
     * Extract all menu keys from navigation structure
     * This ensures we only show permissions for menus that actually exist in navigation
     */
    private function getMenuKeysFromNavigation()
    {
        $menuKeys = [];
        
        // Define navigation structure (same as in navigation.blade.php)
        $menuGroups = [
            ['type' => 'single', 'menu_key' => 'dashboard'],
            ['type' => 'single', 'menu_key' => 'part-erp'],
            [
                'type' => 'group',
                'menu_key' => 'location',
                'children' => [
                    ['menu_key' => 'plants'],
                    ['menu_key' => 'processes'],
                    ['menu_key' => 'lines'],
                    ['menu_key' => 'room-erp'],
                ]
            ],
            [
                'type' => 'group',
                'menu_key' => 'machinary',
                'children' => [
                    ['menu_key' => 'systems'],
                    ['menu_key' => 'groups'],
                    ['menu_key' => 'machine-types'],
                    ['menu_key' => 'brands'],
                    ['menu_key' => 'models'],
                    ['menu_key' => 'machine-erp'],
                    ['menu_key' => 'mutasi'],
                ]
            ],
            [
                'type' => 'group',
                'menu_key' => 'downtime',
                'children' => [
                    ['menu_key' => 'problems'],
                    ['menu_key' => 'reasons'],
                    ['menu_key' => 'actions'],
                    ['menu_key' => 'downtime-erp2'],
                    ['menu_key' => 'work-orders'],
                ]
            ],
            [
                'type' => 'group',
                'menu_key' => 'production',
                'children' => [
                    ['menu_key' => 'production-hourly'],
                ]
            ],
            [
                'type' => 'group',
                'menu_key' => 'users',
                'children' => [
                    ['menu_key' => 'users-list'],
                    ['menu_key' => 'organizational-structure'],
                    ['menu_key' => 'activities'],
                    // 'permissions' is only for admin, but we can still manage it
                ]
            ],
            [
                'type' => 'group',
                'menu_key' => 'preventive-maintenance',
                'children' => [
                    ['menu_key' => 'preventive-scheduling'],
                    ['menu_key' => 'preventive-controlling'],
                    ['menu_key' => 'preventive-monitoring'],
                    ['menu_key' => 'preventive-updating'],
                    ['menu_key' => 'preventive-reporting'],
                ]
            ],
            [
                'type' => 'group',
                'menu_key' => 'predictive-maintenance',
                'children' => [
                    ['menu_key' => 'standards'],
                    ['menu_key' => 'predictive-scheduling'],
                    ['menu_key' => 'predictive-controlling'],
                    ['menu_key' => 'predictive-monitoring'],
                    ['menu_key' => 'predictive-updating'],
                    ['menu_key' => 'predictive-reporting'],
                ]
            ],
            [
                'type' => 'group',
                'menu_key' => 'reports',
                'children' => [
                    ['menu_key' => 'mttr-mtbf'],
                    ['menu_key' => 'pareto-machine'],
                    ['menu_key' => 'summary-downtime'],
                    ['menu_key' => 'mechanic-performance'],
                    ['menu_key' => 'root-cause-analysis'],
                ]
            ],
        ];
        
        // Extract all menu keys (both parent and children) in navigation order
        // Maintain the exact order as in navigation.blade.php
        foreach ($menuGroups as $menu) {
            // Add parent menu key if it exists (for group menus, parent is shown first)
            if (isset($menu['menu_key'])) {
                // Only add if not already added (to avoid duplicates)
                if (!in_array($menu['menu_key'], $menuKeys)) {
                    $menuKeys[] = $menu['menu_key'];
                }
            }
            
            // Add children menu keys in order
            if (isset($menu['children']) && is_array($menu['children'])) {
                foreach ($menu['children'] as $child) {
                    if (isset($child['menu_key'])) {
                        // Only add if not already added (to avoid duplicates)
                        if (!in_array($child['menu_key'], $menuKeys)) {
                            $menuKeys[] = $child['menu_key'];
                        }
                    }
                }
            }
        }
        
        // Return menu keys in navigation order (no sorting, maintain original order)
        return $menuKeys;
    }

    /**
     * Update permissions
     */
    public function update(Request $request)
    {
        // Permissions can be empty if all checkboxes are unchecked
        $request->validate([
            'permissions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();
            
            // Clear existing permissions (delete instead of truncate to work with transactions)
            RolePermission::query()->delete();
            
            // Insert new permissions
            // The request structure is: permissions[menu_key][role][allowed] = "1"
            // Only checked checkboxes are sent, unchecked ones are omitted
            $permissionsData = [];
            
            if ($request->has('permissions') && is_array($request->permissions)) {
                foreach ($request->permissions as $menuKey => $rolePermissions) {
                    if (is_array($rolePermissions)) {
                        foreach ($rolePermissions as $role => $permission) {
                            // Check if checkbox is checked (value is "1" or true)
                            $isAllowed = false;
                            if (isset($permission['allowed'])) {
                                $isAllowed = $permission['allowed'] == '1' || $permission['allowed'] === true || $permission['allowed'] === 'true';
                            }
                            
                            // Get role and menu_key from hidden inputs or from array keys
                            $permissionRole = $permission['role'] ?? $role;
                            $permissionMenuKey = $permission['menu_key'] ?? $menuKey;
                            
                            // Only add if checkbox is checked
                            if ($isAllowed && !empty($permissionRole) && !empty($permissionMenuKey)) {
                                $permissionsData[] = [
                                    'role' => $permissionRole,
                                    'menu_key' => $permissionMenuKey,
                                    'allowed' => true,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                }
            }
            
            // Bulk insert for better performance
            // If no permissions are checked, this will result in an empty table (all permissions removed)
            if (!empty($permissionsData)) {
                RolePermission::insert($permissionsData);
            }
            
            DB::commit();
            
            $count = count($permissionsData);
            return redirect()->route('permissions.index')
                ->with('success', "Permissions updated successfully. {$count} permission(s) saved.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Permission update failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('permissions.index')
                ->with('error', 'Failed to update permissions: ' . $e->getMessage());
        }
    }

    /**
     * Get menu labels for display
     */
    private function getMenuLabels()
    {
        return [
            'dashboard' => 'Dashboard',
            'location' => 'Location',
            'plants' => 'Plants',
            'processes' => 'Processes',
            'lines' => 'Lines',
            'room-erp' => 'Room ERP',
            'machinary' => 'Machinary',
            'systems' => 'Systems',
            'groups' => 'Groups',
            'machine-types' => 'Machine Types',
            'brands' => 'Brands',
            'models' => 'Models',
            'machine-erp' => 'Machine ERP',
            'mutasi' => 'Mutasi',
            'downtime' => 'Downtime',
            'problems' => 'Problems',
            'reasons' => 'Reasons',
            'actions' => 'Actions',
            'downtime-erp2' => 'Downtime ERP2',
            'work-orders' => 'Work Orders',
            'production' => 'Production',
            'production-hourly' => 'Production Hourly',
            'users' => 'Users',
            'users-list' => 'Users List',
            'organizational-structure' => 'Organizational Structure',
            'activities' => 'Activities',
            'preventive-maintenance' => 'Preventive Maintenance',
            'preventive-scheduling' => 'Preventive Scheduling',
            'preventive-controlling' => 'Preventive Controlling',
            'preventive-monitoring' => 'Preventive Monitoring',
            'preventive-updating' => 'Preventive Updating',
            'preventive-reporting' => 'Preventive Reporting',
            'predictive-maintenance' => 'Predictive Maintenance',
            'standards' => 'Standards',
            'predictive-scheduling' => 'Predictive Scheduling',
            'predictive-controlling' => 'Predictive Controlling',
            'predictive-monitoring' => 'Predictive Monitoring',
            'predictive-updating' => 'Predictive Updating',
            'predictive-reporting' => 'Predictive Reporting',
            'reports' => 'Reports',
            'mttr-mtbf' => 'MTTR & MTBF',
            'pareto-machine' => 'Pareto Machine',
            'summary-downtime' => 'Summary Downtime',
            'mechanic-performance' => 'Mechanic Performance',
            'root-cause-analysis' => 'Root Cause Analysis',
            'part-erp' => 'Part ERP',
        ];
    }
}
