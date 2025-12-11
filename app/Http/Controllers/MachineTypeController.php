<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineType;
use App\Models\MaintenancePoint;
use App\Models\Group;
use App\Models\System;
use App\Models\Standard;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class MachineTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $machine_types = MachineType::with(['groupRelation', 'systems'])
                                    ->withCount('maintenancePoints')
                                    ->orderBy('name', 'asc')
                                    ->paginate(12);
        return view('machinary.group.index', compact('machine_types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = Group::with('systems')->orderBy('name')->get();
        $systems = System::orderBy('nama_sistem')->get();
        $standards = Standard::active()->orderBy('name')->get();
        return view('machinary.group.create', compact('groups', 'systems', 'standards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255|unique:machine_types,name',
            'model' => 'nullable|string|max:255',
            'group_id' => 'nullable|exists:groups,id',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ];
        
        // Only validate photo if file is actually uploaded
        if ($request->hasFile('photo')) {
            $validationRules['photo'] = 'required|file|mimes:jpeg,jpg,png,gif|max:5120'; // Max 5MB
        }
        
        $validated = $request->validate($validationRules);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            // Additional validation: check if it's actually an image
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileMime = $photo->getMimeType();
            
            if (!in_array($fileMime, $allowedMimes)) {
                return back()->withErrors(['photo' => 'File harus berupa gambar (JPEG, PNG, JPG, GIF, atau WebP). MIME type yang diterima: ' . $fileMime]);
            }
            
            // Convert to WebP
            $photoPath = ImageHelper::convertToWebP($photo, 'machine-types', 85);
            $validated['photo'] = $photoPath;
        }
        
        $machineType = MachineType::create($validated);
        
        // Auto-sync systems from group if group_id is provided
        if ($request->filled('group_id')) {
            $group = Group::with('systems')->find($request->group_id);
            if ($group && $group->systems->isNotEmpty()) {
                $machineType->systems()->sync($group->systems->pluck('id')->toArray());
            }
        } elseif ($request->filled('systems')) {
            // If systems are manually selected, use those
            $machineType->systems()->sync($request->input('systems', []));
        }

        // Handle maintenance points if provided
        if ($request->has('maintenance_points')) {
            foreach ($request->maintenance_points as $index => $point) {
                if (!empty($point['name'])) {
                    $photoPath = null;
                    // Handle photo upload if exists
                    if ($request->hasFile("maintenance_points.{$index}.photo")) {
                        $photo = $request->file("maintenance_points.{$index}.photo");
                        $photoPath = ImageHelper::convertToWebP($photo, 'maintenance-points', 85);
                    }
                    
                    MaintenancePoint::create([
                        'machine_type_id' => $machineType->id,
                        'category' => $point['category'] ?? 'preventive',
                        'standard_id' => ($point['category'] === 'predictive' && isset($point['standard_id'])) ? $point['standard_id'] : null,
                        'frequency_type' => $point['frequency_type'] ?? null,
                        'frequency_value' => $point['frequency_value'] ?? 1,
                        'name' => $point['name'],
                        'instruction' => $point['instruction'] ?? null,
                        'sequence' => $point['sequence'] ?? 0,
                        'photo' => $photoPath,
                    ]);
                }
            }
        }

        return redirect()->route('machine-types.index')->with('success', 'Machine Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $machineType = MachineType::with([
            'groupRelation',
            'systems',
            'maintenancePoints' => function($query) {
                $query->orderBy('category')->orderBy('sequence');
            }
        ])->findOrFail($id);
        
        // Group points by category
        $points = $machineType->maintenancePoints->groupBy('category');
        
        return view('machinary.group.show', compact('machineType', 'points'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $machineType = MachineType::with(['maintenancePoints' => function($query) {
            $query->orderBy('category')->orderBy('sequence');
        }, 'groupRelation.systems', 'systems'])->findOrFail($id);
        
        // Group points by category
        $points = $machineType->maintenancePoints->groupBy('category');
        
        $groups = Group::with('systems')->orderBy('name')->get();
        $systems = System::orderBy('nama_sistem')->get();
        $standards = Standard::active()->orderBy('name')->get();
        
        return view('machinary.group.edit', compact('machineType', 'points', 'groups', 'systems', 'standards'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validationRules = [
            'name' => 'required|string|max:255|unique:machine_types,name,' . $id,
            'model' => 'nullable|string|max:255',
            'group_id' => 'nullable|exists:groups,id',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ];
        
        // Only validate photo if file is actually uploaded
        if ($request->hasFile('photo')) {
            $validationRules['photo'] = 'required|file|mimes:jpeg,jpg,png,gif|max:5120'; // Max 5MB
        }
        
        $validated = $request->validate($validationRules);
        
        $machineType = MachineType::findOrFail($id);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            // Additional validation: check if it's actually an image
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileMime = $photo->getMimeType();
            
            if (!in_array($fileMime, $allowedMimes)) {
                return back()->withErrors(['photo' => 'File harus berupa gambar (JPEG, PNG, JPG, GIF, atau WebP). MIME type yang diterima: ' . $fileMime]);
            }
            
            // Delete old photo if exists
            ImageHelper::deleteOldImage($machineType->photo);
            
            // Convert to WebP
            $photoPath = ImageHelper::convertToWebP($photo, 'machine-types', 85);
            $validated['photo'] = $photoPath;
        }
        
        $machineType->update($validated);
        
        // Auto-sync systems from group if group_id is provided
        if ($request->filled('group_id')) {
            $group = Group::with('systems')->find($request->group_id);
            if ($group && $group->systems->isNotEmpty()) {
                $machineType->systems()->sync($group->systems->pluck('id')->toArray());
            } else {
                // If group has no systems, clear systems
                $machineType->systems()->sync([]);
            }
        } elseif ($request->has('systems')) {
            // If systems are manually selected, use those
            $machineType->systems()->sync($request->input('systems', []));
        }

        // Handle maintenance points if provided
        if ($request->has('maintenance_points')) {
            foreach ($request->maintenance_points as $point) {
                if (!empty($point['name'])) {
                    if (isset($point['id']) && $point['id']) {
                        // Update existing point
                        $existingPoint = MaintenancePoint::find($point['id']);
                        if ($existingPoint && $existingPoint->machine_type_id == $machineType->id) {
                            $existingPoint->update([
                                'category' => $point['category'] ?? 'preventive',
                                'name' => $point['name'],
                                'instruction' => $point['instruction'] ?? null,
                                'sequence' => $point['sequence'] ?? 0,
                            ]);
                        }
                    } else {
                        // Create new point
                        MaintenancePoint::create([
                            'machine_type_id' => $machineType->id,
                            'category' => $point['category'] ?? 'preventive',
                            'standard_id' => ($point['category'] === 'predictive' && isset($point['standard_id'])) ? $point['standard_id'] : null,
                            'name' => $point['name'],
                            'instruction' => $point['instruction'] ?? null,
                            'sequence' => $point['sequence'] ?? 0,
                            'duration' => $point['duration'] ?? null,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('machine-types.index')->with('success', 'Machine Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $machineType = MachineType::findOrFail($id);
        $machineType->delete();
        return redirect()->route('machine-types.index')->with('success', 'Machine Type deleted successfully.');
    }

    /**
     * Store a maintenance point for a machine type.
     */
    public function storeMaintenancePoint(Request $request, $machineTypeId)
    {
        $validated = $request->validate([
            'category' => 'required|in:autonomous,preventive,predictive',
            'frequency_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly,custom',
            'frequency_value' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255',
            'instruction' => 'nullable|string',
            'sequence' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $machineType = MachineType::findOrFail($machineTypeId);

        $photoPath = null;
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoPath = ImageHelper::convertToWebP($photo, 'maintenance-points', 85);
        }

        MaintenancePoint::create([
            'machine_type_id' => $machineType->id,
            'category' => $validated['category'],
            'standard_id' => ($validated['category'] === 'predictive' && isset($validated['standard_id'])) ? $validated['standard_id'] : null,
            'frequency_type' => $validated['frequency_type'] ?? null,
            'frequency_value' => $validated['frequency_value'] ?? 1,
            'name' => $validated['name'],
            'instruction' => $validated['instruction'] ?? null,
            'sequence' => $validated['sequence'] ?? 0,
            'duration' => $validated['duration'] ?? null,
            'photo' => $photoPath,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Point maintenance berhasil ditambahkan.'
            ]);
        }

        return redirect()->route('machine-types.edit', $machineType->id)
            ->with('success', 'Point maintenance berhasil ditambahkan.');
    }

    /**
     * Update a maintenance point.
     */
    public function updateMaintenancePoint(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'required|in:autonomous,preventive,predictive',
            'standard_id' => 'nullable|exists:standards,id',
            'frequency_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly,custom',
            'frequency_value' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255',
            'instruction' => 'nullable|string',
            'sequence' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $point = MaintenancePoint::findOrFail($id);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            ImageHelper::deleteOldImage($point->photo);
            
            $photo = $request->file('photo');
            // Convert to WebP
            $photoPath = ImageHelper::convertToWebP($photo, 'maintenance-points', 85);
            $validated['photo'] = $photoPath;
        }
        
        // Only set standard_id if category is predictive
        if ($validated['category'] === 'predictive' && !empty($validated['standard_id'])) {
            $validated['standard_id'] = $validated['standard_id'];
        } else {
            $validated['standard_id'] = null;
        }
        
        $point->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Point maintenance berhasil diupdate.'
            ]);
        }

        return redirect()->route('machine-types.edit', $point->machine_type_id)
            ->with('success', 'Point maintenance berhasil diupdate.');
    }

    /**
     * Delete a maintenance point.
     */
    public function destroyMaintenancePoint($id)
    {
        $point = MaintenancePoint::findOrFail($id);
        $machineTypeId = $point->machine_type_id;
        
        // Delete photo if exists
        if ($point->photo && Storage::disk('public')->exists($point->photo)) {
            Storage::disk('public')->delete($point->photo);
        }
        
        $point->delete();

        return redirect()->route('machine-types.edit', $machineTypeId)
            ->with('success', 'Point maintenance berhasil dihapus.');
    }

    /**
     * Import machine types from machine_erp table
     * This will create machine types from unique type_name in machine_erp
     * Handles case-insensitive duplicates and normalizes names
     */
    public function importFromMachineErp()
    {
        try {
            // Get all type_name from machine_erp (not distinct, to handle case variations)
            $allTypeNames = \App\Models\MachineErp::whereNotNull('type_name')
                ->where('type_name', '!=', '')
                ->pluck('type_name')
                ->toArray();
            
            // Normalize and group by case-insensitive name
            $normalizedMap = [];
            foreach ($allTypeNames as $typeName) {
                $normalized = trim($typeName);
                if (empty($normalized)) {
                    continue;
                }
                
                // Use lowercase for comparison, but keep original for display
                $key = strtolower($normalized);
                if (!isset($normalizedMap[$key])) {
                    $normalizedMap[$key] = [
                        'original' => $normalized,
                        'variations' => []
                    ];
                }
                
                // Track all variations
                if (!in_array($normalized, $normalizedMap[$key]['variations'])) {
                    $normalizedMap[$key]['variations'][] = $normalized;
                }
            }
            
            $created = 0;
            $skipped = 0;
            $merged = 0;
            
            foreach ($normalizedMap as $key => $data) {
                $normalizedName = $data['original'];
                
                // Check if machine type already exists (case-insensitive)
                $existing = MachineType::whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])->first();
                
                if (!$existing) {
                    // Get sample data from machine_erp for this type (try all variations)
                    $sample = null;
                    foreach ($data['variations'] as $variation) {
                        $sample = \App\Models\MachineErp::where('type_name', $variation)->first();
                        if ($sample) {
                            break;
                        }
                    }
                    
                    MachineType::create([
                        'name' => $normalizedName,
                        'brand' => $sample->brand_name ?? null,
                        'model' => $sample->model_name ?? null,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                    
                    // If existing name is different (case variation), update all machine_erp to use existing
                    if (strtolower($existing->name) !== strtolower($normalizedName)) {
                        $merged++;
                        // Update all variations to point to existing machine type
                        foreach ($data['variations'] as $variation) {
                            \App\Models\MachineErp::where('type_name', $variation)
                                ->whereNull('machine_type_id')
                                ->update(['machine_type_id' => $existing->id]);
                        }
                    }
                }
            }
            
            // Update machine_erp records to link with machine_types (for all variations)
            foreach ($normalizedMap as $key => $data) {
                $normalizedName = $data['original'];
                
                // Find machine type (case-insensitive)
                $machineType = MachineType::whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])->first();
                
                if ($machineType) {
                    // Update all variations of this type name
                    foreach ($data['variations'] as $variation) {
                        \App\Models\MachineErp::where('type_name', $variation)
                            ->whereNull('machine_type_id')
                            ->update(['machine_type_id' => $machineType->id]);
                    }
                }
            }
            
            $message = "Imported $created new machine types from machine_erp. $skipped already existed.";
            if ($merged > 0) {
                $message .= " $merged duplicates merged with existing types.";
            }
            
            return redirect()->route('machine-types.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing machine types from machine_erp: ' . $e->getMessage());
            return redirect()->route('machine-types.index')->withErrors(['error' => 'Error importing machine types: ' . $e->getMessage()]);
        }
    }

    /**
     * Merge duplicate machine types (case-insensitive)
     * This will merge duplicates and update all related machine_erp records
     */
    public function mergeDuplicates()
    {
        try {
            // Get all machine types
            $allTypes = MachineType::all();
            
            // Group by lowercase name
            $grouped = [];
            foreach ($allTypes as $type) {
                $key = strtolower(trim($type->name));
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [];
                }
                $grouped[$key][] = $type;
            }
            
            $merged = 0;
            $deleted = 0;
            
            foreach ($grouped as $key => $types) {
                if (count($types) > 1) {
                    // Keep the first one (or the one with most data)
                    usort($types, function($a, $b) {
                        // Prefer the one with group_id or more systems
                        $aScore = ($a->group_id ? 10 : 0) + $a->systems()->count();
                        $bScore = ($b->group_id ? 10 : 0) + $b->systems()->count();
                        return $bScore <=> $aScore;
                    });
                    
                    $keep = $types[0];
                    $duplicates = array_slice($types, 1);
                    
                    // Update all machine_erp records to use the kept type
                    foreach ($duplicates as $duplicate) {
                        \App\Models\MachineErp::where('machine_type_id', $duplicate->id)
                            ->update(['machine_type_id' => $keep->id]);
                        
                        // Merge systems if any
                        $keepSystems = $keep->systems()->pluck('systems.id')->toArray();
                        $dupSystems = $duplicate->systems()->pluck('systems.id')->toArray();
                        $allSystems = array_unique(array_merge($keepSystems, $dupSystems));
                        $keep->systems()->sync($allSystems);
                        
                        // Update name if kept one is uppercase but duplicate has better case
                        if (strtoupper($keep->name) === $keep->name && strtolower($duplicate->name) !== $duplicate->name) {
                            $keep->update(['name' => $duplicate->name]);
                        }
                        
                        // Delete duplicate
                        $duplicate->delete();
                        $deleted++;
                    }
                    
                    $merged++;
                }
            }
            
            $message = "Merged $merged groups of duplicates. $deleted duplicate machine types deleted.";
            return redirect()->route('machine-types.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error merging duplicate machine types: ' . $e->getMessage());
            return redirect()->route('machine-types.index')->withErrors(['error' => 'Error merging duplicates: ' . $e->getMessage()]);
        }
    }
}
