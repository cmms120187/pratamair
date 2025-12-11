<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing permissions
        DB::table('role_permissions')->truncate();
        
        $permissions = [
            // Mekanik permissions
            ['role' => 'mekanik', 'menu_key' => 'activities', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'dashboard', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'downtime', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'downtime-erp2', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'location', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'machinary', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'machine-erp', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'mutasi', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'predictive-maintenance', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'predictive-updating', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'preventive-maintenance', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'preventive-updating', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'room-erp', 'allowed' => 1],
            ['role' => 'mekanik', 'menu_key' => 'work-orders', 'allowed' => 1],
            
            // Team Leader permissions
            ['role' => 'team_leader', 'menu_key' => 'activities', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'dashboard', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'downtime', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'downtime-erp2', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'location', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'machinary', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'machine-erp', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'mechanic-performance', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'mttr-mtbf', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'mutasi', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'predictive-maintenance', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'predictive-monitoring', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'predictive-updating', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'preventive-maintenance', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'preventive-monitoring', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'reports', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'room-erp', 'allowed' => 1],
            ['role' => 'team_leader', 'menu_key' => 'work-orders', 'allowed' => 1],
            
            // Group Leader permissions
            ['role' => 'group_leader', 'menu_key' => 'activities', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'dashboard', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'downtime', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'downtime-erp2', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'location', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'machinary', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'machine-erp', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'mechanic-performance', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'mttr-mtbf', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'mutasi', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'pareto-machine', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'predictive-controlling', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'predictive-maintenance', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'predictive-monitoring', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'predictive-reporting', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'preventive-controlling', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'preventive-maintenance', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'preventive-monitoring', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'preventive-reporting', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'reports', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'room-erp', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'summary-downtime', 'allowed' => 1],
            ['role' => 'group_leader', 'menu_key' => 'work-orders', 'allowed' => 1],
            
            // Coordinator permissions
            ['role' => 'coordinator', 'menu_key' => 'activities', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'dashboard', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'downtime', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'downtime-erp2', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'location', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'machinary', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'machine-erp', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'mechanic-performance', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'mttr-mtbf', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'pareto-machine', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'predictive-maintenance', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'predictive-monitoring', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'predictive-reporting', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'predictive-scheduling', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'preventive-controlling', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'preventive-maintenance', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'preventive-monitoring', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'preventive-reporting', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'preventive-scheduling', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'reports', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'room-erp', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'root-cause-analysis', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'summary-downtime', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'users', 'allowed' => 1],
            ['role' => 'coordinator', 'menu_key' => 'users-list', 'allowed' => 1],
            
            // AST Manager permissions
            ['role' => 'ast_manager', 'menu_key' => 'activities', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'dashboard', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'downtime', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'downtime-erp2', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'location', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'machinary', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'machine-erp', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'mechanic-performance', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'mttr-mtbf', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'organizational-structure', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'pareto-machine', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'predictive-maintenance', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'predictive-reporting', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'preventive-maintenance', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'preventive-reporting', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'reports', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'room-erp', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'root-cause-analysis', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'standards', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'summary-downtime', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'users', 'allowed' => 1],
            ['role' => 'ast_manager', 'menu_key' => 'users-list', 'allowed' => 1],
            
            // Manager permissions
            ['role' => 'manager', 'menu_key' => 'activities', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'dashboard', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'downtime', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'downtime-erp2', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'location', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'machinary', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'machine-erp', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'mechanic-performance', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'mttr-mtbf', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'organizational-structure', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'pareto-machine', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'predictive-maintenance', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'predictive-reporting', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'preventive-maintenance', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'preventive-reporting', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'reports', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'room-erp', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'root-cause-analysis', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'standards', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'summary-downtime', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'users', 'allowed' => 1],
            ['role' => 'manager', 'menu_key' => 'users-list', 'allowed' => 1],
            
            // General Manager permissions
            ['role' => 'general_manager', 'menu_key' => 'activities', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'dashboard', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'downtime', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'downtime-erp2', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'location', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'machinary', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'machine-erp', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'mechanic-performance', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'mttr-mtbf', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'organizational-structure', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'pareto-machine', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'predictive-maintenance', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'predictive-reporting', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'preventive-maintenance', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'preventive-reporting', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'reports', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'room-erp', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'root-cause-analysis', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'standards', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'summary-downtime', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'users', 'allowed' => 1],
            ['role' => 'general_manager', 'menu_key' => 'users-list', 'allowed' => 1],
        ];
        
        DB::table('role_permissions')->insert($permissions);
    }
}
