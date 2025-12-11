<?php

namespace App\Http\Controllers;

use App\Models\MaintenancePoint;
use App\Models\MachineType;
use App\Models\Standard;
use Illuminate\Http\Request;

class MaintenancePointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $machineTypes = MachineType::withCount('maintenancePoints')
            ->orderBy('name', 'asc')
            ->paginate(12);
        return view('maintenance-points.index', compact('machineTypes'));
    }

    /**
     * Show the form for managing maintenance points for a specific machine type.
     */
    public function manage($machineTypeId)
    {
        $machineType = MachineType::findOrFail($machineTypeId);
        
        // Get maintenance points grouped by category
        $points = $machineType->maintenancePoints()
            ->with('standard')
            ->orderBy('category')
            ->orderBy('sequence')
            ->get()
            ->groupBy('category');

        // Get standards for predictive maintenance points
        $standards = Standard::active()->orderBy('name')->get();

        return view('maintenance-points.manage', compact('machineType', 'points', 'standards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $machineTypeId)
    {
        $validated = $request->validate([
            'category' => 'required|in:autonomous,preventive,predictive',
            'frequency_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly,custom',
            'frequency_value' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255',
            'instruction' => 'nullable|string',
            'sequence' => 'nullable|integer|min:0',
        ]);

        $machineType = MachineType::findOrFail($machineTypeId);

        MaintenancePoint::create([
            'machine_type_id' => $machineType->id,
            'category' => $validated['category'],
            'standard_id' => ($validated['category'] === 'predictive' && isset($validated['standard_id'])) ? $validated['standard_id'] : null,
            'frequency_type' => $validated['frequency_type'] ?? null,
            'frequency_value' => $validated['frequency_value'] ?? 1,
            'name' => $validated['name'],
            'instruction' => $validated['instruction'] ?? null,
            'sequence' => $validated['sequence'] ?? 0,
        ]);

        return redirect()->route('maintenance-points.manage', $machineType->id)
            ->with('success', 'Point maintenance berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'required|in:autonomous,preventive,predictive',
            'standard_id' => 'nullable|exists:standards,id',
            'frequency_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly,custom',
            'frequency_value' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255',
            'instruction' => 'nullable|string',
            'sequence' => 'nullable|integer|min:0',
        ]);

        $point = MaintenancePoint::findOrFail($id);
        
        // Only set standard_id if category is predictive
        if ($validated['category'] === 'predictive' && isset($validated['standard_id'])) {
            $validated['standard_id'] = $validated['standard_id'];
        } else {
            $validated['standard_id'] = null;
        }
        
        $point->update($validated);

        return redirect()->route('maintenance-points.manage', $point->machine_type_id)
            ->with('success', 'Point maintenance berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $point = MaintenancePoint::findOrFail($id);
        $machineTypeId = $point->machine_type_id;
        $point->delete();

        return redirect()->route('maintenance-points.manage', $machineTypeId)
            ->with('success', 'Point maintenance berhasil dihapus.');
    }
}
