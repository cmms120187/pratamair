<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use App\Models\Process;
use Illuminate\Support\Facades\DB;

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

    /**
     * Preview unique processes from downtime erp or downtime erp2
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
                $uniqueProcesses = DowntimeErp::whereNotNull('process')
                    ->where('process', '!=', '')
                    ->distinct()
                    ->pluck('process')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            } else {
                $uniqueProcesses = DowntimeErp2::whereNotNull('process')
                    ->where('process', '!=', '')
                    ->distinct()
                    ->pluck('process')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            }

            $existingProcesses = Process::whereIn('name', $uniqueProcesses->toArray())->pluck('name')->toArray();
            $newProcesses = $uniqueProcesses->filter(function($name) use ($existingProcesses) {
                return !in_array($name, $existingProcesses);
            })->values();

            return response()->json([
                'success' => true,
                'data_source' => $dataSource,
                'total_unique' => $uniqueProcesses->count(),
                'existing_count' => count($existingProcesses),
                'new_count' => $newProcesses->count(),
                'sample_data' => $uniqueProcesses->take(20)->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract unique processes from downtime erp or downtime erp2
     */
    public function extractFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('processes.index')
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
                $uniqueProcesses = DowntimeErp::whereNotNull('process')
                    ->where('process', '!=', '')
                    ->distinct()
                    ->pluck('process')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            } else {
                $uniqueProcesses = DowntimeErp2::whereNotNull('process')
                    ->where('process', '!=', '')
                    ->distinct()
                    ->pluck('process')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            }

            DB::beginTransaction();

            foreach ($uniqueProcesses as $processName) {
                if (empty($processName)) {
                    continue;
                }

                // Check if process already exists (case-insensitive)
                $existingProcess = Process::whereRaw('LOWER(name) = ?', [strtolower($processName)])->first();

                if ($existingProcess) {
                    $skipped++;
                    continue;
                }

                // Create new process
                try {
                    Process::create([
                        'name' => $processName,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create process '{$processName}': " . $e->getMessage();
                }
            }

            DB::commit();

            return redirect()->route('processes.index')
                ->with('success', "Extracted {$created} new processes from {$dataSource}. {$skipped} already existed.")
                ->with('extraction_details', [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('processes.index')
                ->with('error', 'Extraction failed: ' . $e->getMessage());
        }
    }
}
