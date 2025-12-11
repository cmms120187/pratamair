<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class OrganizationalStructureController extends Controller
{
    /**
     * Display organizational structure
     */
    public function index()
    {
        // Define hierarchy levels (from bottom to top)
        $hierarchy = [
            'mekanik' => ['level' => 1, 'name' => 'Mekanik (Team Member)'],
            'team_leader' => ['level' => 2, 'name' => 'Team Leader'],
            'group_leader' => ['level' => 3, 'name' => 'Group Leader'],
            'coordinator' => ['level' => 4, 'name' => 'Coordinator/Supervisor'],
            'ast_manager' => ['level' => 5, 'name' => 'Assistant Manager'],
            'manager' => ['level' => 6, 'name' => 'Manager'],
            'general_manager' => ['level' => 7, 'name' => 'General Manager'],
        ];
        
        // Get all users with their atasan and bawahan relationships
        $users = User::with('atasan', 'bawahan')->orderBy('name', 'asc')->get();
        
        // Group users by role
        $usersByRole = [];
        foreach ($users as $user) {
            $role = $user->role ?? 'mekanik';
            if (!isset($usersByRole[$role])) {
                $usersByRole[$role] = [];
            }
            $usersByRole[$role][] = $user;
        }
        
        // Build organizational structure tree
        $structure = [];
        foreach ($hierarchy as $roleKey => $roleInfo) {
            $structure[$roleKey] = [
                'level' => $roleInfo['level'],
                'name' => $roleInfo['name'],
                'users' => $usersByRole[$roleKey] ?? [],
            ];
        }
        
        // Get top level (users without atasan or highest level)
        $topLevelUsers = $users->filter(function($user) {
            return $user->atasan_id === null;
        });
        
        return view('users.organizational-structure', compact('structure', 'hierarchy', 'topLevelUsers'));
    }

    /**
     * Display organizational structure chart
     */
    public function chart()
    {
        // Define hierarchy levels (from bottom to top)
        $hierarchy = [
            'mekanik' => ['level' => 1, 'name' => 'Mekanik (Team Member)'],
            'team_leader' => ['level' => 2, 'name' => 'Team Leader'],
            'group_leader' => ['level' => 3, 'name' => 'Group Leader'],
            'coordinator' => ['level' => 4, 'name' => 'Coordinator/Supervisor'],
            'ast_manager' => ['level' => 5, 'name' => 'Assistant Manager'],
            'manager' => ['level' => 6, 'name' => 'Manager'],
            'general_manager' => ['level' => 7, 'name' => 'General Manager'],
        ];
        
        // Get all users with their atasan and bawahan relationships
        $users = User::with('atasan', 'bawahan')->orderBy('name', 'asc')->get();
        
        // Build tree structure starting from top level
        $topLevelUsers = $users->filter(function($user) {
            return $user->atasan_id === null;
        });
        
        // Build tree data for chart
        $buildTree = function($user) use (&$buildTree, $users) {
            $bawahan = $users->filter(function($u) use ($user) {
                return $u->atasan_id === $user->id;
            });
            
            $node = [
                'id' => $user->id,
                'name' => $user->name,
                'nik' => $user->nik ?? '-',
                'email' => $user->email,
                'role' => $user->role,
                'photo' => $user->photo,
                'atasan' => $user->atasan ? [
                    'id' => $user->atasan->id,
                    'name' => $user->atasan->name,
                ] : null,
                'children' => []
            ];
            
            foreach ($bawahan as $b) {
                $node['children'][] = $buildTree($b);
            }
            
            return $node;
        };
        
        $treeData = [];
        foreach ($topLevelUsers as $topUser) {
            $treeData[] = $buildTree($topUser);
        }
        
        // If no top level users, get all users and build from highest role
        if (empty($treeData)) {
            $highestRoleUsers = $users->filter(function($user) use ($hierarchy) {
                $userRole = $user->role ?? 'mekanik';
                $userLevel = $hierarchy[$userRole]['level'] ?? 1;
                // Find users with highest level
                $maxLevel = $users->map(function($u) use ($hierarchy) {
                    $r = $u->role ?? 'mekanik';
                    return $hierarchy[$r]['level'] ?? 1;
                })->max();
                return $userLevel == $maxLevel;
            });
            
            foreach ($highestRoleUsers as $user) {
                $treeData[] = $buildTree($user);
            }
        }
        
        return view('users.organizational-structure-chart', compact('treeData', 'hierarchy', 'users'));
    }
}
