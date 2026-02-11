<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use App\Models\Plant;
use Illuminate\Support\Facades\DB;

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

    /**
     * Preview unique plants from downtime erp or downtime erp2
     */
    public function previewFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin can access this feature.',
            ], 403);
        }

        $request->validate([
            'data_source' => 'required|in:downtime_erp,downtime_erp2',
        ]);

        $dataSource = $request->data_source;

        try {
            if ($dataSource === 'downtime_erp') {
                $uniquePlants = DowntimeErp::whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->distinct()
                    ->pluck('plant')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            } else {
                $uniquePlants = DowntimeErp2::whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->distinct()
                    ->pluck('plant')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            }

            $existingPlants = Plant::whereIn('name', $uniquePlants->toArray())->pluck('name')->toArray();
            $newPlants = $uniquePlants->filter(function($name) use ($existingPlants) {
                return !in_array($name, $existingPlants);
            })->values();

            return response()->json([
                'success' => true,
                'data_source' => $dataSource,
                'total_unique' => $uniquePlants->count(),
                'existing_count' => count($existingPlants),
                'new_count' => $newPlants->count(),
                'sample_data' => $uniquePlants->take(20)->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract unique plants from downtime erp or downtime erp2
     */
    public function extractFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('plants.index')
                ->with('error', 'Unauthorized. Only admin can access this feature.');
        }

        $request->validate([
            'data_source' => 'required|in:downtime_erp,downtime_erp2',
        ]);

        $dataSource = $request->data_source;
        $created = 0;
        $skipped = 0;
        $errors = [];

        try {
            if ($dataSource === 'downtime_erp') {
                $uniquePlants = DowntimeErp::whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->distinct()
                    ->pluck('plant')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            } else {
                $uniquePlants = DowntimeErp2::whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->distinct()
                    ->pluck('plant')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            }

            DB::beginTransaction();

            foreach ($uniquePlants as $plantName) {
                if (empty($plantName)) {
                    continue;
                }

                // Check if plant already exists (case-insensitive)
                $existingPlant = Plant::whereRaw('LOWER(name) = ?', [strtolower($plantName)])->first();

                if ($existingPlant) {
                    $skipped++;
                    continue;
                }

                // Create new plant
                try {
                    Plant::create([
                        'name' => $plantName,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create plant '{$plantName}': " . $e->getMessage();
                }
            }

            DB::commit();

            return redirect()->route('plants.index')
                ->with('success', "Extracted {$created} new plants from {$dataSource}. {$skipped} already existed.")
                ->with('extraction_details', [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('plants.index')
                ->with('error', 'Extraction failed: ' . $e->getMessage());
        }
    }
}
