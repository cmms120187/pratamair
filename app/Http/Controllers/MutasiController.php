<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mutasi;
use App\Models\MachineErp;
use App\Models\RoomErp;

class MutasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $mutasis = Mutasi::with(['machineErp', 'oldRoomErp', 'newRoomErp'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('mutasi.index', compact('mutasis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machineErps = MachineErp::orderBy('idMachine', 'asc')->get();
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        
        // Prepare machine data for JavaScript
        $machinesData = $machineErps->map(function($m) {
            return [
                'id' => (string)$m->id,
                'idMachine' => $m->idMachine ?? '',
                'room_name' => $m->room_name ?? '',
                'plant_name' => $m->plant_name ?? '',
                'process_name' => $m->process_name ?? '',
                'line_name' => $m->line_name ?? '',
            ];
        })->values()->all();
        
        // Prepare room data for JavaScript
        $roomsData = $roomErps->map(function($r) {
            return [
                'id' => (string)$r->id,
                'name' => $r->name ?? '',
                'kode_room' => $r->kode_room ?? '',
            ];
        })->values()->all();
        
        return view('mutasi.create', compact('machineErps', 'roomErps', 'machinesData', 'roomsData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'old_room_erp_id' => 'nullable|exists:room_erp,id',
            'new_room_erp_id' => 'required|exists:room_erp,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Get machine ERP and new room ERP
        $machineErp = MachineErp::findOrFail($validated['machine_erp_id']);
        $newRoomErp = RoomErp::findOrFail($validated['new_room_erp_id']);
        
        // Get old room ERP if exists
        $oldRoomErp = null;
        if ($validated['old_room_erp_id']) {
            $oldRoomErp = RoomErp::findOrFail($validated['old_room_erp_id']);
        } else {
            // Try to find old room by matching room_name in machine_erp
            if ($machineErp->room_name) {
                $oldRoomErp = RoomErp::where('name', $machineErp->room_name)->first();
                if ($oldRoomErp) {
                    $validated['old_room_erp_id'] = $oldRoomErp->id;
                }
            }
        }

        // Create mutasi record
        $mutasi = Mutasi::create($validated);

        // Update machine ERP with new room information
        $machineErp->update([
            'room_name' => $newRoomErp->name,
            'plant_name' => $newRoomErp->plant_name,
            'process_name' => $newRoomErp->process_name,
            'line_name' => $newRoomErp->line_name,
        ]);

        return redirect()->route('mutasi.index')->with('success', 'Mutasi berhasil dibuat dan room ERP telah diupdate.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mutasi = Mutasi::with(['machineErp', 'oldRoomErp', 'newRoomErp'])->findOrFail($id);
        return view('mutasi.show', compact('mutasi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $mutasi = Mutasi::findOrFail($id);
        $machineErps = MachineErp::orderBy('idMachine', 'asc')->get();
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        $page = $request->query('page', 1);
        return view('mutasi.edit', compact('mutasi', 'machineErps', 'roomErps', 'page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $mutasi = Mutasi::findOrFail($id);
        
        $validated = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'old_room_erp_id' => 'nullable|exists:room_erp,id',
            'new_room_erp_id' => 'required|exists:room_erp,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Get machine ERP and new room ERP
        $machineErp = MachineErp::findOrFail($validated['machine_erp_id']);
        $newRoomErp = RoomErp::findOrFail($validated['new_room_erp_id']);

        // Update mutasi record
        $mutasi->update($validated);

        // Update machine ERP with new room information
        $machineErp->update([
            'room_name' => $newRoomErp->name,
            'plant_name' => $newRoomErp->plant_name,
            'process_name' => $newRoomErp->process_name,
            'line_name' => $newRoomErp->line_name,
        ]);

        // Redirect back to the same page if page parameter exists
        $page = $request->input('page');
        if ($page) {
            return redirect()->route('mutasi.index', ['page' => $page])->with('success', 'Mutasi berhasil diupdate dan room ERP telah diupdate.');
        }
        
        return redirect()->route('mutasi.index')->with('success', 'Mutasi berhasil diupdate dan room ERP telah diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mutasi = Mutasi::findOrFail($id);
        $mutasi->delete();
        return redirect()->route('mutasi.index')->with('success', 'Mutasi berhasil dihapus.');
    }
    
    /**
     * Get rooms data for AJAX request (used in downtimeERP2 form)
     */
    public function getRooms()
    {
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        
        $roomsData = $roomErps->map(function($r) {
            return [
                'id' => (string)$r->id,
                'kode_room' => $r->kode_room ?? '',
                'name' => $r->name ?? '',
                'plant_name' => $r->plant_name ?? '',
                'process_name' => $r->process_name ?? '',
                'line_name' => $r->line_name ?? '',
            ];
        })->values()->all();
        
        return response()->json([
            'success' => true,
            'rooms' => $roomsData
        ]);
    }
    
    /**
     * Store mutasi from downtimeERP2 form (AJAX)
     */
    public function storeFromDowntime(Request $request)
    {
        $validated = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'new_room_erp_id' => 'required|exists:room_erp,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            // Get machine ERP and new room ERP
            $machineErp = MachineErp::findOrFail($validated['machine_erp_id']);
            $newRoomErp = RoomErp::findOrFail($validated['new_room_erp_id']);
            
            // Get old room ERP if exists
            $oldRoomErp = null;
            if ($machineErp->room_name) {
                $oldRoomErp = RoomErp::where('name', $machineErp->room_name)->first();
            }
            
            // Prepare mutasi data
            $mutasiData = [
                'machine_erp_id' => $validated['machine_erp_id'],
                'old_room_erp_id' => $oldRoomErp ? $oldRoomErp->id : null,
                'new_room_erp_id' => $validated['new_room_erp_id'],
                'date' => $validated['date'],
                'reason' => $validated['reason'] ?? null,
                'description' => $validated['description'] ?? null,
            ];
            
            // Fix: use correct column name
            $mutasiData['old_room_erp_id'] = $oldRoomErp ? $oldRoomErp->id : null;
            
            // Create mutasi record
            $mutasi = Mutasi::create($mutasiData);

            // Update machine ERP with new room information
            $machineErp->update([
                'kode_room' => $newRoomErp->kode_room,
                'room_name' => $newRoomErp->name,
                'plant_name' => $newRoomErp->plant_name,
                'process_name' => $newRoomErp->process_name,
                'line_name' => $newRoomErp->line_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mutasi berhasil dibuat dan lokasi mesin telah diupdate.',
                'machine' => [
                    'kode_room' => $machineErp->kode_room,
                    'room_name' => $machineErp->room_name,
                    'plant_name' => $machineErp->plant_name,
                    'process_name' => $machineErp->process_name,
                    'line_name' => $machineErp->line_name,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error storing mutasi from downtime: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan mutasi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show bulk scan page for mass mutation
     */
    public function bulkScan()
    {
        // Get all room ERP for new location selection
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        
        // Prepare room data for JavaScript
        $roomsData = $roomErps->map(function($r) {
            return [
                'id' => (string)$r->id,
                'kode_room' => $r->kode_room ?? '',
                'name' => $r->name ?? '',
                'plant_name' => $r->plant_name ?? '',
                'process_name' => $r->process_name ?? '',
                'line_name' => $r->line_name ?? '',
            ];
        })->values()->all();
        
        // Get all machine ERP for suggestion dropdown
        $machineErps = MachineErp::orderBy('idMachine', 'asc')->get();
        
        // Prepare machine data for JavaScript
        $machinesData = $machineErps->map(function($m) {
            return [
                'id' => (string)$m->id,
                'idMachine' => $m->idMachine ?? '',
                'typeMachine' => $m->type_name ?? '',
                'modelMachine' => $m->model_name ?? '',
                'brandMachine' => $m->brand_name ?? '',
                'plant' => $m->plant_name ?? '',
                'process' => $m->process_name ?? '',
                'line' => $m->line_name ?? '',
                'roomName' => $m->room_name ?? '',
                'kodeRoom' => $m->kode_room ?? '',
            ];
        })->values()->all();
        
        return view('mutasi.bulk-scan', compact('roomErps', 'roomsData', 'machinesData'));
    }
    
    /**
     * Scan machine by ID Machine (AJAX)
     */
    public function scanMachine(Request $request)
    {
        $validated = $request->validate([
            'idMachine' => 'required|string|max:255',
        ]);
        
        try {
            $machineErp = MachineErp::where('idMachine', $validated['idMachine'])->first();
            
            if (!$machineErp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mesin dengan ID Machine "' . $validated['idMachine'] . '" tidak ditemukan.'
                ], 404);
            }
            
            // Get current room if exists
            $currentRoom = null;
            if ($machineErp->room_name) {
                $currentRoom = RoomErp::where('name', $machineErp->room_name)->first();
            }
            
            return response()->json([
                'success' => true,
                'machine' => [
                    'id' => (string)$machineErp->id,
                    'idMachine' => $machineErp->idMachine ?? '',
                    'type_name' => $machineErp->type_name ?? '',
                    'brand_name' => $machineErp->brand_name ?? '',
                    'model_name' => $machineErp->model_name ?? '',
                    'current_location' => [
                        'plant_name' => $machineErp->plant_name ?? '',
                        'process_name' => $machineErp->process_name ?? '',
                        'line_name' => $machineErp->line_name ?? '',
                        'room_name' => $machineErp->room_name ?? '',
                        'kode_room' => $machineErp->kode_room ?? '',
                    ],
                    'current_room_id' => $currentRoom ? (string)$currentRoom->id : null,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error scanning machine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memindai mesin: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store bulk mutations (AJAX)
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'mutations' => 'required|array|min:1',
            'mutations.*.machine_erp_id' => 'required|exists:machine_erp,id',
            'mutations.*.new_room_erp_id' => 'required|exists:room_erp,id',
            'mutations.*.date' => 'required|date',
            'mutations.*.reason' => 'nullable|string|max:255',
            'mutations.*.description' => 'nullable|string',
        ]);
        
        try {
            $successCount = 0;
            $errors = [];
            
            foreach ($validated['mutations'] as $index => $mutationData) {
                try {
                    // Get machine ERP and new room ERP
                    $machineErp = MachineErp::findOrFail($mutationData['machine_erp_id']);
                    $newRoomErp = RoomErp::findOrFail($mutationData['new_room_erp_id']);
                    
                    // Get old room ERP if exists
                    $oldRoomErp = null;
                    if ($machineErp->room_name) {
                        $oldRoomErp = RoomErp::where('name', $machineErp->room_name)->first();
                    }
                    
                    // Prepare mutasi data
                    $mutasiData = [
                        'machine_erp_id' => $mutationData['machine_erp_id'],
                        'old_room_erp_id' => $oldRoomErp ? $oldRoomErp->id : null,
                        'new_room_erp_id' => $mutationData['new_room_erp_id'],
                        'date' => $mutationData['date'],
                        'reason' => $mutationData['reason'] ?? null,
                        'description' => $mutationData['description'] ?? null,
                    ];
                    
                    // Create mutasi record
                    $mutasi = Mutasi::create($mutasiData);
                    
                    // Update machine ERP with new room information
                    $machineErp->update([
                        'kode_room' => $newRoomErp->kode_room,
                        'room_name' => $newRoomErp->name,
                        'plant_name' => $newRoomErp->plant_name,
                        'process_name' => $newRoomErp->process_name,
                        'line_name' => $newRoomErp->line_name,
                    ]);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'idMachine' => $machineErp->idMachine ?? 'Unknown',
                        'message' => $e->getMessage()
                    ];
                    \Log::error('Error storing mutation ' . $index . ': ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan $successCount dari " . count($validated['mutations']) . " mutasi.",
                'success_count' => $successCount,
                'total_count' => count($validated['mutations']),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            \Log::error('Error storing bulk mutations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan mutasi massal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store bulk mutations with same status and location (simplified mode)
     */
    public function bulkStoreSimple(Request $request)
    {
        $validated = $request->validate([
            'machine_ids' => 'required|array|min:1',
            'machine_ids.*' => 'required|exists:machine_erp,id',
            'status' => 'required|in:Running,Standby,Damage,Destroy,Other',
            'new_room_erp_id' => 'required|exists:room_erp,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        try {
            $successCount = 0;
            $errors = [];
            $newRoomErp = RoomErp::findOrFail($validated['new_room_erp_id']);
            
            foreach ($validated['machine_ids'] as $index => $machineId) {
                try {
                    // Get machine ERP
                    $machineErp = MachineErp::findOrFail($machineId);
                    
                    // Get old room ERP if exists
                    $oldRoomErp = null;
                    if ($machineErp->room_name) {
                        $oldRoomErp = RoomErp::where('name', $machineErp->room_name)->first();
                    }
                    
                    // Prepare mutasi data
                    $mutasiData = [
                        'machine_erp_id' => $machineId,
                        'old_room_erp_id' => $oldRoomErp ? $oldRoomErp->id : null,
                        'new_room_erp_id' => $validated['new_room_erp_id'],
                        'date' => $validated['date'],
                        'reason' => $validated['reason'] ?? null,
                        'description' => $validated['description'] ?? null,
                    ];
                    
                    // Create mutasi record
                    $mutasi = Mutasi::create($mutasiData);
                    
                    // Update machine ERP with new room information and status
                    $machineErp->update([
                        'kode_room' => $newRoomErp->kode_room,
                        'room_name' => $newRoomErp->name,
                        'plant_name' => $newRoomErp->plant_name,
                        'process_name' => $newRoomErp->process_name,
                        'line_name' => $newRoomErp->line_name,
                        'status' => $validated['status'],
                    ]);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'idMachine' => $machineErp->idMachine ?? 'Unknown',
                        'message' => $e->getMessage()
                    ];
                    \Log::error('Error storing simple bulk mutation ' . $index . ': ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan $successCount dari " . count($validated['machine_ids']) . " mutasi.",
                'success_count' => $successCount,
                'total_count' => count($validated['machine_ids']),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            \Log::error('Error storing simple bulk mutations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan mutasi massal: ' . $e->getMessage()
            ], 500);
        }
    }
}
