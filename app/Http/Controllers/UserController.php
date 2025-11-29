<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\User::with('atasan');
        
        // Filter by role (jabatan)
        if ($request->filled('filter_role')) {
            $query->where('role', $request->filter_role);
        }
        
        // Filter by atasan
        if ($request->filled('filter_atasan')) {
            $query->where('atasan_id', $request->filter_atasan);
        }
        
        // Sort berdasarkan NIK terendah (ascending), null di akhir
        $users = $query->orderByRaw('CASE WHEN nik IS NULL THEN 1 ELSE 0 END')
            ->orderBy('nik', 'asc')
            ->paginate(12)
            ->withQueryString();
        
        // Pre-load atasan untuk optimasi (hindari N+1 query) - untuk fallback
        $teamLeaders = \App\Models\User::where('role', 'team_leader')->get()->keyBy('id');
        $groupLeaders = \App\Models\User::where('role', 'group_leader')->get()->keyBy('id');
        $coordinators = \App\Models\User::where('role', 'coordinator')->get()->keyBy('id');
        
        // Get all users for batch update dropdown (atasan)
        $allUsers = \App\Models\User::select('id', 'name', 'role')
            ->whereIn('role', ['team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager'])
            ->orderBy('name')
            ->get();
        
        // Prepare atasan options for JavaScript
        $roleMap = [
            'team_leader' => 'Team Leader',
            'group_leader' => 'Group Leader',
            'coordinator' => 'Coordinator',
            'ast_manager' => 'Assistant Manager',
            'manager' => 'Manager',
            'general_manager' => 'General Manager'
        ];
        
        $atasanOptions = $allUsers->map(function($user) use ($roleMap) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'roleDisplay' => $roleMap[$user->role] ?? $user->role
            ];
        })->values()->all();
        
        return view('users.index', compact('users', 'teamLeaders', 'groupLeaders', 'coordinators', 'allUsers', 'atasanOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua user untuk dropdown atasan
        $teamLeaders = \App\Models\User::where('role', 'team_leader')->get();
        $groupLeaders = \App\Models\User::where('role', 'group_leader')->get();
        $coordinators = \App\Models\User::where('role', 'coordinator')->get();
        $astManagers = \App\Models\User::where('role', 'ast_manager')->get();
        $managers = \App\Models\User::where('role', 'manager')->get();
        $generalManagers = \App\Models\User::where('role', 'general_manager')->get();
        
        return view('users.create', compact('teamLeaders', 'groupLeaders', 'coordinators', 'astManagers', 'managers', 'generalManagers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:5|unique:users,nik|regex:/^[0-9]{5}$/',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:mekanik,team_leader,group_leader,coordinator,ast_manager,manager,general_manager,admin',
            'atasan_id' => 'nullable|exists:users,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Admin tidak perlu atasan
        if ($validated['role'] === 'admin') {
            $validated['atasan_id'] = null;
        }
        
        // Validasi atasan sesuai hierarki (skip untuk admin)
        if ($validated['role'] !== 'admin' && $request->filled('atasan_id')) {
            $atasan = \App\Models\User::findOrFail($request->atasan_id);
            $roleHierarchy = [
                'mekanik' => 'team_leader',
                'team_leader' => 'group_leader',
                'group_leader' => 'coordinator',
                'coordinator' => 'ast_manager',
                'ast_manager' => 'manager',
                'manager' => 'general_manager',
            ];
            
            if (isset($roleHierarchy[$validated['role']])) {
                $expectedAtasanRole = $roleHierarchy[$validated['role']];
                if ($atasan->role !== $expectedAtasanRole) {
                    return back()->withErrors(['atasan_id' => 'Atasan harus memiliki role ' . $expectedAtasanRole])->withInput();
                }
            }
        }
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('users', 'public');
        }
        
        $user = new \App\Models\User();
        $user->nik = $validated['nik'];
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password']; // Model akan otomatis hash karena 'password' => 'hashed' di casts
        $user->role = $validated['role'];
        $user->atasan_id = $validated['atasan_id'] ?? null;
        $user->photo = $validated['photo'] ?? null;
        $user->save();
        
        return redirect()->route('users.index', request()->only(['filter_role', 'filter_atasan', 'page']))->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Ambil semua user untuk dropdown atasan
        $teamLeaders = \App\Models\User::where('role', 'team_leader')->where('id', '!=', $id)->get();
        $groupLeaders = \App\Models\User::where('role', 'group_leader')->where('id', '!=', $id)->get();
        $coordinators = \App\Models\User::where('role', 'coordinator')->where('id', '!=', $id)->get();
        $astManagers = \App\Models\User::where('role', 'ast_manager')->where('id', '!=', $id)->get();
        $managers = \App\Models\User::where('role', 'manager')->where('id', '!=', $id)->get();
        $generalManagers = \App\Models\User::where('role', 'general_manager')->where('id', '!=', $id)->get();
        
        return view('users.edit', compact('user', 'teamLeaders', 'groupLeaders', 'coordinators', 'astManagers', 'managers', 'generalManagers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:5|unique:users,nik,' . $id . '|regex:/^[0-9]{5}$/',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string|in:mekanik,team_leader,group_leader,coordinator,ast_manager,manager,general_manager,admin',
            'atasan_id' => 'nullable|exists:users,id',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Admin tidak perlu atasan
        if ($validated['role'] === 'admin') {
            $validated['atasan_id'] = null;
        }
        
        // Validasi atasan sesuai hierarki (skip untuk admin)
        if ($validated['role'] !== 'admin' && $request->filled('atasan_id')) {
            $atasan = \App\Models\User::findOrFail($request->atasan_id);
            $roleHierarchy = [
                'mekanik' => 'team_leader',
                'team_leader' => 'group_leader',
                'group_leader' => 'coordinator',
                'coordinator' => 'ast_manager',
                'ast_manager' => 'manager',
                'manager' => 'general_manager',
            ];
            
            if (isset($roleHierarchy[$validated['role']])) {
                $expectedAtasanRole = $roleHierarchy[$validated['role']];
                if ($atasan->role !== $expectedAtasanRole) {
                    return back()->withErrors(['atasan_id' => 'Atasan harus memiliki role ' . $expectedAtasanRole])->withInput();
                }
            }
        }
        
        $user = \App\Models\User::findOrFail($id);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('users', 'public');
        }
        
        $user->nik = $validated['nik'];
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->atasan_id = $validated['atasan_id'] ?? null;
        
        if ($request->filled('password')) {
            $user->password = $validated['password']; // Model akan otomatis hash karena 'password' => 'hashed' di casts
        }
        
        if (isset($validated['photo'])) {
            $user->photo = $validated['photo'];
        }
        
        $user->save();
        return redirect()->route('users.index', request()->only(['filter_role', 'filter_atasan', 'page']))->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index', $request->only(['filter_role', 'filter_atasan', 'page']))->with('success', 'User deleted successfully.');
    }

    /**
     * Batch update users
     */
    public function batchUpdate(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'field' => 'required|in:role,atasan_id',
            'value' => 'required',
        ]);

        $userIds = $validated['user_ids'];
        $field = $validated['field'];
        $value = $validated['value'];

        // Get users to update
        $users = \App\Models\User::whereIn('id', $userIds)->get();

        $updatedCount = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                if ($field === 'role') {
                    // Validate role value
                    if (!in_array($value, ['mekanik', 'team_leader', 'group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager'])) {
                        $errors[] = "Invalid role value for user {$user->name}";
                        continue;
                    }
                    $user->role = $value;
                    
                    // Clear atasan_id if role changed (will need to be reassigned)
                    if ($user->atasan_id) {
                        $oldAtasan = \App\Models\User::find($user->atasan_id);
                        if ($oldAtasan) {
                            $roleHierarchy = [
                                'mekanik' => 'team_leader',
                                'team_leader' => 'group_leader',
                                'group_leader' => 'coordinator',
                                'coordinator' => 'ast_manager',
                                'ast_manager' => 'manager',
                                'manager' => 'general_manager',
                            ];
                            if (isset($roleHierarchy[$value]) && $oldAtasan->role !== $roleHierarchy[$value]) {
                                $user->atasan_id = null;
                            }
                        }
                    }
                } elseif ($field === 'atasan_id') {
                    // Validate atasan exists and has correct role
                    $atasan = \App\Models\User::find($value);
                    if (!$atasan) {
                        $errors[] = "Atasan not found for user {$user->name}";
                        continue;
                    }
                    
                    // Validate hierarchy
                    $roleHierarchy = [
                        'mekanik' => 'team_leader',
                        'team_leader' => 'group_leader',
                        'group_leader' => 'coordinator',
                        'coordinator' => 'ast_manager',
                        'ast_manager' => 'manager',
                        'manager' => 'general_manager',
                    ];
                    
                    if (isset($roleHierarchy[$user->role])) {
                        $expectedAtasanRole = $roleHierarchy[$user->role];
                        if ($atasan->role !== $expectedAtasanRole) {
                            $errors[] = "Atasan untuk user {$user->name} harus memiliki role {$expectedAtasanRole}";
                            continue;
                        }
                    }
                    
                    $user->atasan_id = $value;
                }

                $user->save();
                $updatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error updating user {$user->name}: " . $e->getMessage();
            }
        }

        $message = "Successfully updated {$updatedCount} user(s).";
        if (count($errors) > 0) {
            $message .= " Errors: " . implode(', ', $errors);
            return redirect()->route('users.index', $request->only(['filter_role', 'filter_atasan', 'page']))->with('error', $message);
        }

        return redirect()->route('users.index', $request->only(['filter_role', 'filter_atasan', 'page']))->with('success', $message);
    }
}
