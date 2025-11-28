<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $processes = \App\Models\Process::orderBy('name', 'asc')->paginate(12);
        return view('processes.index', compact('processes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('processes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $process = new \App\Models\Process();
        $process->name = $validated['name'];
        $process->save();
        return redirect()->route('processes.index')->with('success', 'Process created successfully.');
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
        $process = \App\Models\Process::findOrFail($id);
        return view('processes.edit', compact('process'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $process = \App\Models\Process::findOrFail($id);
        $process->name = $validated['name'];
        $process->save();
        return redirect()->route('processes.index')->with('success', 'Process updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $process = \App\Models\Process::findOrFail($id);
        $process->delete();
        return redirect()->route('processes.index')->with('success', 'Process deleted successfully.');
    }

    /**
     * Import processes from room_erp table
     * This will create processes from unique process_name in room_erp
     * Handles case-insensitive duplicates
     */
    public function importFromRoomErp()
    {
        try {
            // Get all process_name from room_erp
            $allProcessNames = \App\Models\RoomErp::whereNotNull('process_name')
                ->where('process_name', '!=', '')
                ->pluck('process_name')
                ->toArray();
            
            // Normalize and group by case-insensitive name
            $normalizedMap = [];
            foreach ($allProcessNames as $processName) {
                $normalized = trim($processName);
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
            
            foreach ($normalizedMap as $key => $processName) {
                // Check if process already exists (case-insensitive)
                $existing = \App\Models\Process::whereRaw('LOWER(name) = ?', [strtolower($processName)])->first();
                
                if (!$existing) {
                    \App\Models\Process::create([
                        'name' => $processName,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }
            
            $message = "Imported $created new processes from room_erp. $skipped already existed.";
            
            return redirect()->route('processes.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing processes from room_erp: ' . $e->getMessage());
            return redirect()->route('processes.index')->withErrors(['error' => 'Error importing processes: ' . $e->getMessage()]);
        }
    }
}
