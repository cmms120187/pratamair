<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lines = \App\Models\Line::with(['plant', 'process'])
            ->orderBy('name', 'asc')
            ->paginate(12);
        return view('lines.index', compact('lines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plants = \App\Models\Plant::orderBy('name', 'asc')->get();
        $processes = \App\Models\Process::orderBy('name', 'asc')->get();
        return view('lines.create', compact('plants', 'processes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'process_id' => 'required|exists:processes,id',
        ]);
        $line = new \App\Models\Line();
        $line->name = $validated['name'];
        $line->plant_id = $validated['plant_id'];
        $line->process_id = $validated['process_id'];
        $line->save();
        return redirect()->route('lines.index')->with('success', 'Line created successfully.');
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
        $line = \App\Models\Line::findOrFail($id);
        $plants = \App\Models\Plant::orderBy('name', 'asc')->get();
        $processes = \App\Models\Process::orderBy('name', 'asc')->get();
        return view('lines.edit', compact('line', 'plants', 'processes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'process_id' => 'required|exists:processes,id',
        ]);
        $line = \App\Models\Line::findOrFail($id);
        $line->name = $validated['name'];
        $line->plant_id = $validated['plant_id'];
        $line->process_id = $validated['process_id'];
        $line->save();
        return redirect()->route('lines.index')->with('success', 'Line updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $line = \App\Models\Line::findOrFail($id);
        $line->delete();
        return redirect()->route('lines.index')->with('success', 'Line deleted successfully.');
    }

    /**
     * Import lines from room_erp table
     * This will create lines from unique line_name in room_erp
     * Handles finding or creating Plant and Process
     */
    public function importFromRoomErp()
    {
        try {
            $created = 0;
            $skipped = 0;
            $errors = 0;

            // Get all room_erp records with plant_name and line_name
            $roomErps = \App\Models\RoomErp::whereNotNull('line_name')
                ->where('line_name', '!=', '')
                ->whereNotNull('plant_name')
                ->where('plant_name', '!=', '')
                ->select('line_name', 'plant_name', 'process_name')
                ->distinct()
                ->get();

            foreach ($roomErps as $roomErp) {
                try {
                    $lineName = trim($roomErp->line_name);
                    $plantName = trim($roomErp->plant_name);
                    $processName = trim($roomErp->process_name ?? '');

                    if (empty($lineName) || empty($plantName)) {
                        $skipped++;
                        continue;
                    }

                    // Find or create Plant
                    $plant = \App\Models\Plant::whereRaw('LOWER(name) = ?', [strtolower($plantName)])->first();
                    if (!$plant) {
                        $plant = \App\Models\Plant::create(['name' => $plantName]);
                    }

                    // Find or create Process (if process_name is provided)
                    $process = null;
                    if (!empty($processName)) {
                        $process = \App\Models\Process::whereRaw('LOWER(name) = ?', [strtolower($processName)])->first();
                        if (!$process) {
                            $process = \App\Models\Process::create(['name' => $processName]);
                        }
                    }

                    // Check if line already exists (by name and plant_id, case-insensitive)
                    $existing = \App\Models\Line::where('plant_id', $plant->id)
                        ->whereRaw('LOWER(name) = ?', [strtolower($lineName)])
                        ->first();

                    if (!$existing) {
                        \App\Models\Line::create([
                            'name' => $lineName,
                            'plant_id' => $plant->id,
                            'process_id' => $process ? $process->id : null,
                        ]);
                        $created++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error('Error importing line from room_erp: ' . $e->getMessage(), [
                        'line_name' => $roomErp->line_name,
                        'plant_name' => $roomErp->plant_name,
                    ]);
                }
            }

            $message = "Imported $created new lines from room_erp. $skipped skipped, $errors errors.";
            return redirect()->route('lines.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing lines from room_erp: ' . $e->getMessage());
            return redirect()->route('lines.index')->withErrors(['error' => 'Error importing lines: ' . $e->getMessage()]);
        }
    }
}
