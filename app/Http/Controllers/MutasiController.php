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
}
