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
            
            // Get all menu keys from PermissionHelper
            $menuPermissions = PermissionHelper::getMenuPermissions();
            $menuKeys = is_array($menuPermissions) ? array_keys($menuPermissions) : [];
            
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
     * Update permissions
     */
    public function update(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        try {
            // Clear existing permissions (delete instead of truncate to work with transactions)
            RolePermission::query()->delete();
            
            // Insert new permissions
            // The request structure is: permissions[menu_key][role][...]
            $permissionsData = [];
            foreach ($request->permissions as $menuKey => $rolePermissions) {
                if (is_array($rolePermissions)) {
                    foreach ($rolePermissions as $role => $permission) {
                        if (isset($permission['allowed']) && $permission['allowed'] == '1' && 
                            isset($permission['role']) && isset($permission['menu_key'])) {
                            $permissionsData[] = [
                                'role' => $permission['role'],
                                'menu_key' => $permission['menu_key'],
                                'allowed' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
            }
            
            // Bulk insert for better performance
            if (!empty($permissionsData)) {
                RolePermission::insert($permissionsData);
            }
            
            return redirect()->route('permissions.index')
                ->with('success', 'Permissions updated successfully.');
        } catch (\Exception $e) {
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
