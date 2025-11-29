<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DowntimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Downtime::with([
            'machine', 
            'machine.plant', 
            'machine.process',
            'machine.line',
            'machine.room', 
            'machine.machineType',
            'machine.model',
            'machine.brand',
            'problem',
            'problemMm',
            'reason',
            'action',
            'group',
            'mekanik', 
            'leader', 
            'coord'
        ]);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Filter by plant
        if ($request->filled('plant_id')) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('plant_id', $request->plant_id);
            });
        }

        // Filter by process
        if ($request->filled('process_id')) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('process_id', $request->process_id);
            });
        }

        // Filter by line
        if ($request->filled('line_id')) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('line_id', $request->line_id);
            });
        }

        // Filter by room
        if ($request->filled('room_id')) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('room_id', $request->room_id);
            });
        }

        // Filter by user role (mekanik only sees their own data)
        if (DataFilterHelper::shouldFilterRoute(request()->route()->getName())) {
            $user = auth()->user();
            if ($user && $user->role === 'mekanik' && $user->id) {
                $query->where('mekanik_id', $user->id);
            }
        }

        // Filter by machine type
        if ($request->filled('machine_type_id')) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('type_id', $request->machine_type_id);
            });
        }

        // Search by machine ID
        if ($request->filled('search_machine')) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('idMachine', 'like', '%' . $request->search_machine . '%');
            });
        }

        $downtimes = $query->orderBy('date', 'desc')
                          ->orderBy('stopProduction', 'desc')
                          ->paginate(12)
                          ->withQueryString();

        // Get filter options
        $plants = \App\Models\Plant::orderBy('name')->get();
        $processes = \App\Models\Process::orderBy('name')->get();
        $lines = \App\Models\Line::orderBy('name')->get();
        $rooms = \App\Models\Room::orderBy('name')->get();
        $machineTypes = \App\Models\MachineType::orderBy('name')->get();

        return view('downtimes.index', compact('downtimes', 'plants', 'processes', 'lines', 'rooms', 'machineTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = \App\Models\Machine::all();
        $problems = \App\Models\Problem::all();
        $problemMms = \App\Models\ProblemMm::all();
        $reasons = \App\Models\Reason::all();
        $actions = \App\Models\Action::all();
        $mechanics = \App\Models\User::where('role', 'mekanik')->orderBy('nik')->get();
        $parts = \App\Models\Part::orderBy('name')->get();
        $plants = \App\Models\Plant::all();
        $processes = \App\Models\Process::all();
        $lines = \App\Models\Line::all();
        $rooms = \App\Models\Room::all();
        return view('downtimes.create', compact('machines', 'problems', 'problemMms', 'reasons', 'actions', 'mechanics', 'parts', 'plants', 'processes', 'lines', 'rooms'));
    }

    /**
     * Get parts by systems (AJAX)
     */
    public function getPartsBySystems(Request $request)
    {
        $systemsParam = $request->input('systems', '');
        
        if (empty($systemsParam)) {
            return response()->json([]);
        }
        
        // Parse comma-separated systems
        $systemNames = is_array($systemsParam) ? $systemsParam : explode(',', $systemsParam);
        $systemNames = array_filter(array_map('trim', $systemNames));
        
        if (empty($systemNames)) {
            return response()->json([]);
        }
        
        // Get system IDs first to ensure systems exist
        $systemIds = \App\Models\System::whereIn('nama_sistem', $systemNames)->pluck('id')->toArray();
        
        if (empty($systemIds)) {
            return response()->json([]);
        }
        
        // Query parts that have any of these systems using system IDs
        // Need to specify table name (systems.id) to avoid ambiguity in join
        $parts = \App\Models\Part::whereHas('systems', function($query) use ($systemIds) {
            $query->whereIn('systems.id', $systemIds);
        })
        ->with('systems')
        ->orderBy('name')
        ->get(['id', 'part_number', 'name', 'brand', 'stock', 'price']);
        
        $data = $parts->map(function($part) {
            return [
                'id' => $part->id,
                'part_number' => $part->part_number,
                'name' => $part->name,
                'brand' => $part->brand,
                'stock' => $part->stock,
                'price' => $part->price,
                'display' => $part->name . ' (' . ($part->part_number ?? 'N/A') . ')'
            ];
        })->values()->toArray();
        
        return response()->json($data);
    }

    /**
     * Get problems by systems (AJAX)
     */
    public function getProblemsBySystems(Request $request)
    {
        $systemsParam = $request->input('systems', '');
        
        if (empty($systemsParam)) {
            return response()->json([]);
        }
        
        // Parse comma-separated systems
        $systemNames = is_array($systemsParam) ? $systemsParam : explode(',', $systemsParam);
        $systemNames = array_filter(array_map('trim', $systemNames));
        
        if (empty($systemNames)) {
            return response()->json([]);
        }
        
        // Get system IDs first to ensure systems exist
        $systemIds = \App\Models\System::whereIn('nama_sistem', $systemNames)->pluck('id')->toArray();
        
        if (empty($systemIds)) {
            return response()->json([]);
        }
        
        // Query problems that have any of these systems using system IDs
        // Need to specify table name (systems.id) to avoid ambiguity in join
        $problems = \App\Models\Problem::whereHas('systems', function($query) use ($systemIds) {
            $query->whereIn('systems.id', $systemIds);
        })
        ->orderBy('problem_header', 'asc')
        ->orderBy('name', 'asc')
        ->get(['id', 'name', 'problem_header', 'problem_mm']);
        
        $data = $problems->map(function($problem) {
            return [
                'id' => $problem->id,
                'name' => $problem->name,
                'problem_header' => $problem->problem_header,
                'problem_mm' => $problem->problem_mm,
                'display' => $problem->name . ($problem->problem_header ? ' (' . $problem->problem_header . ')' : '')
            ];
        })->values()->toArray();
        
        return response()->json($data);
    }

    /**
     * Search mechanics by NIK (AJAX)
     */
    public function searchMechanic(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $mechanics = \App\Models\User::where('role', 'mekanik')
            ->where(function($q) use ($query) {
                $q->where('nik', 'like', '%' . $query . '%')
                  ->orWhere('name', 'like', '%' . $query . '%');
            })
            ->orderBy('nik')
            ->limit(10)
            ->get(['id', 'nik', 'name']);
        
        return response()->json($mechanics);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|integer',
            'date' => 'required|date',
            'stopProduction' => 'required|date_format:H:i:s',
            'responMechanic' => 'required|date_format:H:i:s',
            'startProduction' => 'required|date_format:H:i:s',
            'duration' => 'required',
            'standard_time' => 'nullable|integer',
            'problem_id' => 'required|integer',
            'problem_mm_id' => 'nullable|integer',
            'reason_id' => 'required|integer',
            'action_id' => 'required|integer',
            'mekanik_id' => 'required|integer',
            'parts' => 'nullable|array',
            'parts.*.part_id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
        ]);
        
        // Gabungkan tanggal dengan waktu
        $date = $validated['date'];
        $stopProduction = \Carbon\Carbon::parse($date . ' ' . $validated['stopProduction']);
        $startProduction = \Carbon\Carbon::parse($date . ' ' . $validated['startProduction']);
        $responMechanic = \Carbon\Carbon::parse($date . ' ' . $validated['responMechanic']);
        
        // Hitung duration dari startProduction - stopProduction (dalam menit)
        $duration = $startProduction->diffInMinutes($stopProduction);
        
        // Get machine untuk auto-fill group_id
        $machine = \App\Models\Machine::with('machineType.groupRelation')->findOrFail($validated['machine_id']);
        $groupId = $machine->machineType->group_id ?? null;
        
        // Get mekanik untuk auto-fill leader dan coordinator
        $mekanik = \App\Models\User::findOrFail($validated['mekanik_id']);
        $leaderId = $mekanik->atasan_id ?? null;
        $coordId = null;
        if ($leaderId) {
            $leader = \App\Models\User::find($leaderId);
            $coordId = $leader->atasan_id ?? null;
        }
        
        $validated['stopProduction'] = $stopProduction;
        $validated['responMechanic'] = $responMechanic;
        $validated['startProduction'] = $startProduction;
        $validated['duration'] = $duration; // Override dengan hasil perhitungan
        $validated['group_id'] = $groupId; // Auto-fill dari machine type
        $validated['leader_id'] = $leaderId;
        $validated['coord_id'] = $coordId;
        $validated['part'] = null; // Part string tidak digunakan lagi, menggunakan relasi
        
        $parts = $request->input('parts', []);
        unset($validated['parts']);
        
        $downtime = new \App\Models\Downtime();
        $downtime->fill($validated);
        $downtime->save();
        
        // Attach parts with quantities
        if (!empty($parts)) {
            $partsData = [];
            foreach ($parts as $part) {
                if (isset($part['part_id']) && isset($part['quantity'])) {
                    $partsData[$part['part_id']] = ['quantity' => (int)$part['quantity']];
                }
            }
            if (!empty($partsData)) {
                $downtime->parts()->attach($partsData);
            }
        }
        
        return redirect()->route('downtimes.index')->with('success', 'Downtime created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $downtime = \App\Models\Downtime::with([
            'machine', 
            'machine.plant', 
            'machine.process',
            'machine.line',
            'machine.room', 
            'machine.machineType',
            'machine.model',
            'machine.brand',
            'problem',
            'problemMm',
            'reason',
            'action',
            'group',
            'mekanik', 
            'leader', 
            'coord'
        ])->findOrFail($id);
        
        $machines = \App\Models\Machine::all();
        $problems = \App\Models\Problem::all();
        $problemMms = \App\Models\ProblemMm::all();
        $reasons = \App\Models\Reason::all();
        $actions = \App\Models\Action::all();
        $groups = \App\Models\Group::all();
        $users = \App\Models\User::all();
        $plants = \App\Models\Plant::all();
        $processes = \App\Models\Process::all();
        $lines = \App\Models\Line::all();
        $rooms = \App\Models\Room::all();
        
        return view('downtimes.show', compact('downtime', 'machines', 'problems', 'problemMms', 'reasons', 'actions', 'groups', 'users', 'plants', 'processes', 'lines', 'rooms'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $page = $request->query('page', 1);
        $downtime = \App\Models\Downtime::with([
            'machine', 
            'machine.plant', 
            'machine.process',
            'machine.line',
            'machine.room', 
            'machine.machineType',
            'machine.model',
            'machine.brand',
            'problem',
            'problemMm',
            'reason',
            'action',
            'group',
            'mekanik', 
            'leader', 
            'coord'
        ])->findOrFail($id);
        
        $machines = \App\Models\Machine::all();
        $problems = \App\Models\Problem::all();
        $problemMms = \App\Models\ProblemMm::all();
        $reasons = \App\Models\Reason::all();
        $actions = \App\Models\Action::all();
        $mechanics = \App\Models\User::where('role', 'mekanik')->orderBy('nik')->get();
        $users = \App\Models\User::all(); // For dropdown in edit view
        $parts = \App\Models\Part::orderBy('name')->get();
        $plants = \App\Models\Plant::all();
        $processes = \App\Models\Process::all();
        $lines = \App\Models\Line::all();
        $rooms = \App\Models\Room::all();
        
        return view('downtimes.edit', compact('downtime', 'machines', 'problems', 'problemMms', 'reasons', 'actions', 'mechanics', 'users', 'parts', 'plants', 'processes', 'lines', 'rooms', 'page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'machine_id' => 'required|integer',
            'date' => 'required|date',
            'stopProduction' => 'required|date_format:H:i:s',
            'responMechanic' => 'required|date_format:H:i:s',
            'startProduction' => 'required|date_format:H:i:s',
            'duration' => 'required',
            'standard_time' => 'nullable|integer',
            'problem_id' => 'required|integer',
            'problem_mm_id' => 'nullable|integer',
            'reason_id' => 'required|integer',
            'action_id' => 'required|integer',
            'mekanik_id' => 'required|integer',
            'parts' => 'nullable|array',
            'parts.*.part_id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
        ]);
        
        // Gabungkan tanggal dengan waktu
        $date = $validated['date'];
        $stopProduction = \Carbon\Carbon::parse($date . ' ' . $validated['stopProduction']);
        $startProduction = \Carbon\Carbon::parse($date . ' ' . $validated['startProduction']);
        $responMechanic = \Carbon\Carbon::parse($date . ' ' . $validated['responMechanic']);
        
        // Hitung duration dari startProduction - stopProduction (dalam menit)
        $duration = $startProduction->diffInMinutes($stopProduction);
        
        // Get machine untuk auto-fill group_id
        $machine = \App\Models\Machine::with('machineType.groupRelation')->findOrFail($validated['machine_id']);
        $groupId = $machine->machineType->group_id ?? null;
        
        // Get mekanik untuk auto-fill leader dan coordinator
        $mekanik = \App\Models\User::findOrFail($validated['mekanik_id']);
        $leaderId = $mekanik->atasan_id ?? null;
        $coordId = null;
        if ($leaderId) {
            $leader = \App\Models\User::find($leaderId);
            $coordId = $leader->atasan_id ?? null;
        }
        
        $validated['stopProduction'] = $stopProduction;
        $validated['responMechanic'] = $responMechanic;
        $validated['startProduction'] = $startProduction;
        $validated['duration'] = $duration; // Override dengan hasil perhitungan
        $validated['group_id'] = $groupId; // Auto-fill dari machine type
        $validated['leader_id'] = $leaderId;
        $validated['coord_id'] = $coordId;
        $validated['part'] = null; // Part string tidak digunakan lagi, menggunakan relasi
        
        $parts = $request->input('parts', []);
        unset($validated['parts']);
        
        $downtime = \App\Models\Downtime::findOrFail($id);
        $downtime->fill($validated);
        $downtime->save();
        
        // Sync parts with quantities
        if (!empty($parts)) {
            $partsData = [];
            foreach ($parts as $part) {
                if (isset($part['part_id']) && isset($part['quantity'])) {
                    $partsData[$part['part_id']] = ['quantity' => (int)$part['quantity']];
                }
            }
            $downtime->parts()->sync($partsData);
        } else {
            $downtime->parts()->detach();
        }
        
        // Get page from request or default to 1
        $page = $request->input('page', 1);
        
        return redirect()->route('downtimes.index', ['page' => $page])
            ->with('success', 'Downtime updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $downtime = \App\Models\Downtime::findOrFail($id);
        $downtime->delete();
        return redirect()->route('downtimes.index')->with('success', 'Downtime deleted successfully.');
    }

    /**
     * Search machine by idMachine
     */
    public function searchMachine(Request $request)
    {
        $idMachine = $request->input('idMachine');
        
        $machine = \App\Models\Machine::with(['plant', 'process', 'line', 'room', 'machineType.groupRelation', 'machineType.systems', 'brand', 'model'])
            ->where('idMachine', $idMachine)
            ->first();
        
        if (!$machine) {
            return response()->json([
                'success' => false,
                'message' => 'Machine not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'machine' => [
                'id' => $machine->id,
                'idMachine' => $machine->idMachine,
                'nama_mesin' => $machine->machineType->name ?? '-',
                'model_mesin' => $machine->model->name ?? '-',
                'brand' => $machine->brand->name ?? '-',
                'room' => $machine->room->name ?? '-',
                'room_id' => $machine->room_id,
                'plant' => $machine->plant->name ?? '-',
                'plant_id' => $machine->plant_id,
                'process' => $machine->process->name ?? '-',
                'process_id' => $machine->process_id,
                'line' => $machine->line->name ?? '-',
                'line_id' => $machine->line_id,
                'group' => $machine->machineType->groupRelation->name ?? '-',
                'systems' => $machine->machineType->systems->pluck('nama_sistem')->toArray(),
            ]
        ]);
    }

    /**
     * Get processes by plant (AJAX)
     */
    public function getProcessesByPlant(Request $request)
    {
        try {
            $plantId = $request->get('plant_id') ?? $request->input('plant_id');
            
            if (!$plantId) {
                return response()->json([], 200);
            }
            
            if (!is_numeric($plantId)) {
                return response()->json(['error' => 'Invalid plant_id'], 400);
            }
            
            // Get distinct processes from lines that belong to this plant
            $processes = \App\Models\Process::whereHas('lines', function($query) use ($plantId) {
                $query->where('plant_id', (int)$plantId);
            })->orderBy('name', 'asc')->get();
            
            $data = $processes->map(function($process) {
                return [
                    'id' => (int)$process->id,
                    'name' => (string)$process->name
                ];
            })->values()->toArray();
            
            return response()->json($data, 200);
        } catch (\Exception $e) {
            \Log::error('Error in getProcessesByPlant: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching processes'], 500);
        }
    }

    /**
     * Get lines by plant and process (AJAX)
     */
    public function getLinesByPlantAndProcess(Request $request)
    {
        try {
            $plantId = $request->get('plant_id') ?? $request->input('plant_id');
            $processId = $request->get('process_id') ?? $request->input('process_id');
            
            if (!$plantId || !$processId) {
                return response()->json([], 200);
            }
            
            if (!is_numeric($plantId) || !is_numeric($processId)) {
                return response()->json(['error' => 'Invalid plant_id or process_id'], 400);
            }
            
            $lines = \App\Models\Line::where('plant_id', (int)$plantId)
                ->where('process_id', (int)$processId)
                ->orderBy('name', 'asc')
                ->get();
            
            $data = $lines->map(function($line) {
                return [
                    'id' => (int)$line->id,
                    'name' => (string)$line->name
                ];
            })->values()->toArray();
            
            return response()->json($data, 200);
        } catch (\Exception $e) {
            \Log::error('Error in getLinesByPlantAndProcess: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching lines'], 500);
        }
    }

    /**
     * Get rooms by plant and line (AJAX) - reuse existing method
     */
    public function getRoomsByPlantAndLine(Request $request)
    {
        try {
            $plantId = $request->get('plant_id') ?? $request->input('plant_id');
            $lineId = $request->get('line_id') ?? $request->input('line_id');
            
            if (!$plantId || !$lineId) {
                return response()->json([], 200);
            }
            
            if (!is_numeric($plantId) || !is_numeric($lineId)) {
                return response()->json(['error' => 'Invalid plant_id or line_id'], 400);
            }
            
            $rooms = \App\Models\Room::where('plant_id', (int)$plantId)
                ->where('line_id', (int)$lineId)
                ->orderBy('name', 'asc')
                ->get();
            
            $data = $rooms->map(function($room) {
                return [
                    'id' => (int)$room->id,
                    'name' => (string)$room->name
                ];
            })->values()->toArray();
            
            return response()->json($data, 200);
        } catch (\Exception $e) {
            \Log::error('Error in getRoomsByPlantAndLine: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching rooms'], 500);
        }
    }

    /**
     * Update machine location (Mutasi)
     */
    public function updateMachineLocation(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|integer|exists:machines,id',
            'plant_id' => 'required|integer|exists:plants,id',
            'process_id' => 'required|integer|exists:processes,id',
            'line_id' => 'required|integer|exists:lines,id',
            'room_id' => 'required|integer|exists:rooms,id',
        ]);
        
        $machine = \App\Models\Machine::findOrFail($validated['machine_id']);
        $machine->plant_id = $validated['plant_id'];
        $machine->process_id = $validated['process_id'];
        $machine->line_id = $validated['line_id'];
        $machine->room_id = $validated['room_id'];
        $machine->save();
        
        // Reload dengan relationships
        $machine->load(['plant', 'process', 'line', 'room']);
        
        return response()->json([
            'success' => true,
            'message' => 'Lokasi mesin berhasil diupdate',
            'machine' => [
                'plant' => $machine->plant->name ?? '-',
                'process' => $machine->process->name ?? '-',
                'line' => $machine->line->name ?? '-',
                'room' => $machine->room->name ?? '-',
            ]
        ]);
    }
}
