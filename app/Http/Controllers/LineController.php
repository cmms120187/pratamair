<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use App\Models\Line;
use App\Models\Plant;
use App\Models\Process;
use Illuminate\Support\Facades\DB;

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

    /**
     * Preview unique lines from downtime erp or downtime erp2
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
                $uniqueLines = DowntimeErp::whereNotNull('line')
                    ->where('line', '!=', '')
                    ->whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->select('line', 'plant', 'process')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'line' => trim($item->line),
                            'plant' => trim($item->plant),
                            'process' => trim($item->process ?? ''),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['line']) && !empty($item['plant']);
                    })
                    ->unique(function($item) {
                        return strtolower($item['line']) . '|' . strtolower($item['plant']) . '|' . strtolower($item['process']);
                    })
                    ->values();
            } else {
                $uniqueLines = DowntimeErp2::whereNotNull('line')
                    ->where('line', '!=', '')
                    ->whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->select('line', 'plant', 'process')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'line' => trim($item->line),
                            'plant' => trim($item->plant),
                            'process' => trim($item->process ?? ''),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['line']) && !empty($item['plant']);
                    })
                    ->unique(function($item) {
                        return strtolower($item['line']) . '|' . strtolower($item['plant']) . '|' . strtolower($item['process']);
                    })
                    ->values();
            }

            // Check existing lines
            $existingCount = 0;
            $newCount = 0;
            foreach ($uniqueLines as $lineData) {
                $plant = Plant::whereRaw('LOWER(name) = ?', [strtolower($lineData['plant'])])->first();
                if (!$plant) {
                    $newCount++;
                    continue;
                }

                $process = null;
                if (!empty($lineData['process'])) {
                    $process = Process::whereRaw('LOWER(name) = ?', [strtolower($lineData['process'])])->first();
                }

                $existingLine = Line::where('plant_id', $plant->id)
                    ->whereRaw('LOWER(name) = ?', [strtolower($lineData['line'])]);
                
                if ($process) {
                    $existingLine->where('process_id', $process->id);
                } else {
                    $existingLine->whereNull('process_id');
                }

                if ($existingLine->exists()) {
                    $existingCount++;
                } else {
                    $newCount++;
                }
            }

            return response()->json([
                'success' => true,
                'data_source' => $dataSource,
                'total_unique' => $uniqueLines->count(),
                'existing_count' => $existingCount,
                'new_count' => $newCount,
                'sample_data' => $uniqueLines->take(20)->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract unique lines from downtime erp or downtime erp2
     */
    public function extractFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('lines.index')
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
                $uniqueLines = DowntimeErp::whereNotNull('line')
                    ->where('line', '!=', '')
                    ->whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->select('line', 'plant', 'process')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'line' => trim($item->line),
                            'plant' => trim($item->plant),
                            'process' => trim($item->process ?? ''),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['line']) && !empty($item['plant']);
                    })
                    ->unique(function($item) {
                        return strtolower($item['line']) . '|' . strtolower($item['plant']) . '|' . strtolower($item['process']);
                    })
                    ->values();
            } else {
                $uniqueLines = DowntimeErp2::whereNotNull('line')
                    ->where('line', '!=', '')
                    ->whereNotNull('plant')
                    ->where('plant', '!=', '')
                    ->select('line', 'plant', 'process')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'line' => trim($item->line),
                            'plant' => trim($item->plant),
                            'process' => trim($item->process ?? ''),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['line']) && !empty($item['plant']);
                    })
                    ->unique(function($item) {
                        return strtolower($item['line']) . '|' . strtolower($item['plant']) . '|' . strtolower($item['process']);
                    })
                    ->values();
            }

            DB::beginTransaction();

            foreach ($uniqueLines as $lineData) {
                try {
                    // Find or create Plant
                    $plant = Plant::whereRaw('LOWER(name) = ?', [strtolower($lineData['plant'])])->first();
                    if (!$plant) {
                        $plant = Plant::create(['name' => $lineData['plant']]);
                    }

                    // Find or create Process (if provided)
                    $process = null;
                    if (!empty($lineData['process'])) {
                        $process = Process::whereRaw('LOWER(name) = ?', [strtolower($lineData['process'])])->first();
                        if (!$process) {
                            $process = Process::create(['name' => $lineData['process']]);
                        }
                    }

                    // Check if line already exists
                    $existingLine = Line::where('plant_id', $plant->id)
                        ->whereRaw('LOWER(name) = ?', [strtolower($lineData['line'])]);
                    
                    if ($process) {
                        $existingLine->where('process_id', $process->id);
                    } else {
                        $existingLine->whereNull('process_id');
                    }

                    if ($existingLine->exists()) {
                        $skipped++;
                        continue;
                    }

                    // Create new line
                    Line::create([
                        'name' => $lineData['line'],
                        'plant_id' => $plant->id,
                        'process_id' => $process ? $process->id : null,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create line '{$lineData['line']}' (Plant: {$lineData['plant']}, Process: {$lineData['process']}): " . $e->getMessage();
                }
            }

            DB::commit();

            return redirect()->route('lines.index')
                ->with('success', "Extracted {$created} new lines from {$dataSource}. {$skipped} already existed.")
                ->with('extraction_details', [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('lines.index')
                ->with('error', 'Extraction failed: ' . $e->getMessage());
        }
    }
}
