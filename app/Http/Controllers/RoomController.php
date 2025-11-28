<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Plant;
use App\Models\Line;
use App\Models\Process;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::with(['plant', 'line'])
            ->leftJoin('plants', 'rooms.plant_id', '=', 'plants.id')
            ->select('rooms.*')
            ->orderBy('plants.name', 'asc')
            ->orderBy('rooms.name', 'asc')
            ->paginate(12);
        return view('rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plants = Plant::all();
        return view('rooms.create', compact('plants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'line_id' => 'required|exists:lines,id',
            'category' => 'nullable|in:Production,Supporting,Warehouse,Other',
            'description' => 'nullable|string',
        ]);

        // Validate that the selected line belongs to the selected plant
        $line = Line::findOrFail($validated['line_id']);
        if ($line->plant_id != $validated['plant_id']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['line_id' => 'Line yang dipilih tidak sesuai dengan Plant yang dipilih.']);
        }
        
        Room::create($validated);
        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
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
        $room = Room::with(['plant', 'line'])->findOrFail($id);
        $plants = Plant::all();
        $lines = Line::where('plant_id', $room->plant_id)->get();
        return view('rooms.edit', compact('room', 'plants', 'lines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'line_id' => 'required|exists:lines,id',
            'category' => 'nullable|in:Production,Supporting,Warehouse,Other',
            'description' => 'nullable|string',
        ]);

        // Validate that the selected line belongs to the selected plant
        $line = Line::findOrFail($validated['line_id']);
        if ($line->plant_id != $validated['plant_id']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['line_id' => 'Line yang dipilih tidak sesuai dengan Plant yang dipilih.']);
        }
        
        $room = Room::findOrFail($id);
        $room->update($validated);
        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }

    /**
     * Get lines by plant_id (for AJAX)
     */
    public function getLinesByPlant(Request $request)
    {
        try {
            $plantId = $request->query('plant_id') ?? $request->input('plant_id');
            
            if (!$plantId) {
                return response()->json([], 200);
            }
            
            if (!is_numeric($plantId)) {
                return response()->json([
                    'error' => 'Invalid plant_id'
                ], 400);
            }
            
            $lines = Line::where('plant_id', (int)$plantId)
                ->orderBy('name', 'asc')
                ->get(['id', 'name']);
            
            $data = $lines->map(function($line) {
                return [
                    'id' => (int)$line->id,
                    'name' => (string)$line->name
                ];
            })->values()->all();
            
            return response()->json($data, 200, [
                'Content-Type' => 'application/json',
            ]);
                
        } catch (\Exception $e) {
            \Log::error('Error in getLinesByPlant: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'plant_id' => $request->get('plant_id')
            ]);
            
            return response()->json([
                'error' => 'Error fetching lines: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import rooms from room_erp table
     * This will create rooms from room_erp data
     * Handles finding or creating Plant and Line
     */
    public function importFromRoomErp()
    {
        try {
            $created = 0;
            $skipped = 0;
            $errors = 0;

            // Get all room_erp records
            $roomErps = \App\Models\RoomErp::all();

            foreach ($roomErps as $roomErp) {
                try {
                    // Find or create Plant
                    $plant = \App\Models\Plant::where('name', $roomErp->plant_name)->first();
                    if (!$plant && $roomErp->plant_name) {
                        $plant = \App\Models\Plant::create(['name' => $roomErp->plant_name]);
                    }

                    if (!$plant) {
                        $skipped++;
                        continue;
                    }

                    // Find or create Line
                    $line = null;
                    if ($roomErp->line_name) {
                        $line = \App\Models\Line::where('plant_id', $plant->id)
                            ->where('name', $roomErp->line_name)
                            ->first();
                        
                        if (!$line) {
                            $line = \App\Models\Line::create([
                                'name' => $roomErp->line_name,
                                'plant_id' => $plant->id,
                            ]);
                        }
                    }

                    if (!$line) {
                        $skipped++;
                        continue;
                    }

                    // Check if room already exists (by name, plant_id, line_id)
                    $existingRoom = Room::where('name', $roomErp->name)
                        ->where('plant_id', $plant->id)
                        ->where('line_id', $line->id)
                        ->first();

                    if (!$existingRoom) {
                        Room::create([
                            'name' => $roomErp->name,
                            'plant_id' => $plant->id,
                            'line_id' => $line->id,
                            'category' => $roomErp->category ?? null,
                            'description' => $roomErp->description ?? null,
                        ]);
                        $created++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error('Error importing room from room_erp ID ' . $roomErp->id . ': ' . $e->getMessage());
                }
            }

            $message = "Imported $created new rooms from room_erp. $skipped skipped, $errors errors.";
            return redirect()->route('rooms.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing rooms from room_erp: ' . $e->getMessage());
            return redirect()->route('rooms.index')->withErrors(['error' => 'Error importing rooms: ' . $e->getMessage()]);
        }
    }

    /**
     * Synchronize rooms with room_erp table
     * This will update room_erp records based on matching data in rooms table
     */
    public function synchronize()
    {
        try {
            $updated = 0;
            $skipped = 0;
            $errors = 0;

            // Get all rooms with relationships
            $rooms = Room::with(['plant', 'line'])->get();

            foreach ($rooms as $room) {
                try {
                    if (!$room->plant || !$room->line) {
                        $skipped++;
                        continue;
                    }

                    // Find room_erp by name (case-insensitive)
                    $roomErp = \App\Models\RoomErp::whereRaw('LOWER(name) = ?', [strtolower($room->name)])->first();

                    if ($roomErp) {
                        // Update room_erp with data from room
                        $updateData = [
                            'plant_name' => $room->plant->name,
                            'line_name' => $room->line->name,
                            'category' => $room->category ?? null,
                            'description' => $room->description ?? null,
                        ];

                        $roomErp->update($updateData);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error('Error synchronizing room ID ' . $room->id . ': ' . $e->getMessage());
                }
            }

            $message = "Synchronization completed. $updated records updated, $skipped skipped, $errors errors.";
            return redirect()->route('rooms.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error synchronizing rooms: ' . $e->getMessage());
            return redirect()->route('rooms.index')->withErrors(['error' => 'Error synchronizing: ' . $e->getMessage()]);
        }
    }
}
