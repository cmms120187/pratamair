<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Machine;
use App\Models\MachineErp;
use App\Models\User;
use App\Models\PartErp;
use App\Helpers\DataFilterHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['machine', 'assignedTo', 'createdBy']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        // Filter by user role (mekanik only sees their own assigned work orders)
        if (DataFilterHelper::shouldFilterRoute(request()->route()->getName())) {
            $user = auth()->user();
            if ($user && $user->role === 'mekanik' && $user->id) {
                $query->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id);
                });
            }
        }

        $workOrders = $query->orderBy('order_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $machines = Machine::orderBy('idMachine')->get();
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->orderBy('name')->get();

        return view('work-orders.index', compact('workOrders', 'machines', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get machines from MachineErp for suggestion/search
        $machineErps = MachineErp::orderBy('idMachine')->get();
        // Also get Machine for dropdown (if needed for form submission)
        $machines = Machine::with(['plant', 'process', 'line', 'room', 'machineType'])->orderBy('idMachine')->get();
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->orderBy('name')->get();
        $parts = PartErp::orderBy('name')->get();
        
        // Map MachineErp data for JavaScript suggestion/search
        $machinesArray = $machineErps->map(function($machineErp) {
            return [
                'idMachine' => $machineErp->idMachine,
                'type_name' => $machineErp->type_name ?? '-',
                'plant_name' => $machineErp->plant_name ?? '-',
                'process_name' => $machineErp->process_name ?? '-',
                'line_name' => $machineErp->line_name ?? '-',
                'room_name' => $machineErp->room_name ?? '-',
                // Get Machine ID if exists (for form submission)
                'machine_id' => Machine::where('idMachine', $machineErp->idMachine)->value('id') ?? null,
            ];
        })->toArray();
        
        $partsArray = $parts->map(function($part) {
            return [
                'id' => $part->id,
                'name' => $part->name,
                'part_number' => $part->part_number ?? '-',
            ];
        })->toArray();
        
        return view('work-orders.create', compact('machines', 'users', 'parts', 'machinesArray', 'partsArray'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'status' => 'nullable|in:pending,waiting_parts,order_parts,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'machine_id' => 'required',
            'description' => 'required|string',
            'problem_description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'photo_before' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle machine_id - if it's idMachine string, find the Machine
        if (!is_numeric($validated['machine_id'])) {
            // It's an idMachine string, find the Machine
            $machine = Machine::where('idMachine', $validated['machine_id'])->first();
            if (!$machine) {
                return redirect()->back()->withInput()->withErrors(['machine_id' => 'Machine dengan ID tersebut belum terdaftar di sistem Machine. Silakan daftarkan terlebih dahulu.']);
            }
            $validated['machine_id'] = $machine->id;
        } else {
            // Validate that machine exists
            if (!Machine::where('id', $validated['machine_id'])->exists()) {
                return redirect()->back()->withInput()->withErrors(['machine_id' => 'Machine tidak ditemukan.']);
            }
        }

        // Generate WO Number
        $woNumber = $this->generateWoNumber();

        // Handle photo upload
        if ($request->hasFile('photo_before')) {
            $validated['photo_before'] = $request->file('photo_before')->store('work-orders', 'public');
        }

        $validated['wo_number'] = $woNumber;
        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['created_by'] = auth()->id();

        WorkOrder::create($validated);

        return redirect()->route('work-orders.index')->with('success', 'Work Order berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $workOrder = WorkOrder::with(['machine.plant', 'machine.process', 'machine.line', 'machine.room', 'machine.machineType', 'assignedTo', 'createdBy'])->findOrFail($id);
        
        return view('work-orders.show', compact('workOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $machines = Machine::with(['plant', 'process', 'line', 'room', 'machineType'])->orderBy('idMachine')->get();
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->orderBy('name')->get();
        
        return view('work-orders.edit', compact('workOrder', 'machines', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'order_date' => 'required|date',
            'status' => 'required|in:pending,waiting_parts,order_parts,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'machine_id' => 'required|exists:machines,id',
            'description' => 'required|string',
            'problem_description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'solution' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'actual_duration_minutes' => 'nullable|integer|min:0',
            'photo_before' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_after' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo uploads
        if ($request->hasFile('photo_before')) {
            if ($workOrder->photo_before && Storage::disk('public')->exists($workOrder->photo_before)) {
                Storage::disk('public')->delete($workOrder->photo_before);
            }
            $validated['photo_before'] = $request->file('photo_before')->store('work-orders', 'public');
        }

        if ($request->hasFile('photo_after')) {
            if ($workOrder->photo_after && Storage::disk('public')->exists($workOrder->photo_after)) {
                Storage::disk('public')->delete($workOrder->photo_after);
            }
            $validated['photo_after'] = $request->file('photo_after')->store('work-orders', 'public');
        }

        // Auto set started_at if status changed to in_progress
        if ($validated['status'] == 'in_progress' && !$workOrder->started_at) {
            $validated['started_at'] = now();
        }

        // Auto set completed_at if status changed to completed
        if ($validated['status'] == 'completed' && !$workOrder->completed_at) {
            $validated['completed_at'] = now();
        }

        // Reset timestamps if status changed from in_progress or completed
        if ($validated['status'] == 'pending' || $validated['status'] == 'cancelled') {
            if ($validated['status'] == 'pending') {
                $validated['started_at'] = null;
                $validated['completed_at'] = null;
            }
        }

        $workOrder->update($validated);

        return redirect()->route('work-orders.index', $request->only(['status', 'priority', 'machine_id', 'assigned_to', 'date_from', 'date_to', 'page']))->with('success', 'Work Order berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $workOrder = WorkOrder::findOrFail($id);

        // Delete photos if exist
        if ($workOrder->photo_before && Storage::disk('public')->exists($workOrder->photo_before)) {
            Storage::disk('public')->delete($workOrder->photo_before);
        }
        if ($workOrder->photo_after && Storage::disk('public')->exists($workOrder->photo_after)) {
            Storage::disk('public')->delete($workOrder->photo_after);
        }

        $workOrder->delete();

        return redirect()->route('work-orders.index', $request->only(['status', 'priority', 'machine_id', 'assigned_to', 'date_from', 'date_to', 'page']))->with('success', 'Work Order berhasil dihapus.');
    }

    /**
     * Generate unique WO Number
     */
    private function generateWoNumber(): string
    {
        $prefix = 'WO-' . date('Ymd') . '-';
        $lastWo = WorkOrder::where('wo_number', 'like', $prefix . '%')
            ->orderBy('wo_number', 'desc')
            ->first();

        if ($lastWo) {
            $lastNumber = (int) Str::after($lastWo->wo_number, $prefix);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
