<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plants = \App\Models\Plant::orderBy('name', 'asc')->paginate(12);
        return view('plants.index', compact('plants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('plants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $plant = new \App\Models\Plant();
        $plant->name = $validated['name'];
        $plant->save();
        return redirect()->route('plants.index')->with('success', 'Plant created successfully.');
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
        $plant = \App\Models\Plant::findOrFail($id);
        return view('plants.edit', compact('plant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $plant = \App\Models\Plant::findOrFail($id);
        $plant->name = $validated['name'];
        $plant->save();
        return redirect()->route('plants.index')->with('success', 'Plant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $plant = \App\Models\Plant::findOrFail($id);
        $plant->delete();
        return redirect()->route('plants.index')->with('success', 'Plant deleted successfully.');
    }

    /**
     * Import plants from room_erp table
     * This will create plants from unique plant_name in room_erp
     * Handles case-insensitive duplicates
     */
    public function importFromRoomErp()
    {
        try {
            // Get all plant_name from room_erp
            $allPlantNames = \App\Models\RoomErp::whereNotNull('plant_name')
                ->where('plant_name', '!=', '')
                ->pluck('plant_name')
                ->toArray();
            
            // Normalize and group by case-insensitive name
            $normalizedMap = [];
            foreach ($allPlantNames as $plantName) {
                $normalized = trim($plantName);
                if (empty($normalized)) {
                    continue;
                }
                
                // Use lowercase for comparison, but keep original for display
                $key = strtolower($normalized);
                if (!isset($normalizedMap[$key])) {
                    $normalizedMap[$key] = $normalized;
                }
            }
            
            $created = 0;
            $skipped = 0;
            
            foreach ($normalizedMap as $key => $plantName) {
                // Check if plant already exists (case-insensitive)
                $existing = \App\Models\Plant::whereRaw('LOWER(name) = ?', [strtolower($plantName)])->first();
                
                if (!$existing) {
                    \App\Models\Plant::create([
                        'name' => $plantName,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }
            
            $message = "Imported $created new plants from room_erp. $skipped already existed.";
            
            return redirect()->route('plants.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing plants from room_erp: ' . $e->getMessage());
            return redirect()->route('plants.index')->withErrors(['error' => 'Error importing plants: ' . $e->getMessage()]);
        }
    }
}
