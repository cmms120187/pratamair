<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Models\InspectionSchedule;
use App\Models\InspectionTemplate;
use App\Models\MachineErp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchedulingController extends Controller
{
    public function index(Request $request)
    {
        $query = InspectionSchedule::with(['machineErp', 'template', 'assignedUser']);
        
        // Filters
        if ($request->filled('machine_type_id')) {
            $query->whereHas('machineErp', function($q) use ($request) {
                $q->where('machine_type_id', $request->machine_type_id);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('machineErp', function($mq) use ($request) {
                      $mq->where('idMachine', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        $schedules = $query->orderBy('start_date', 'desc')->paginate(20);
        $machineTypes = \App\Models\MachineType::orderBy('name')->get();
        
        return view('inspections.scheduling.index', compact('schedules', 'machineTypes'));
    }

    public function create(Request $request)
    {
        $machines = MachineErp::with('machineType')->orderBy('idMachine')->get();
        $templates = InspectionTemplate::where('status', 'active')->with('machineType')->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        return view('inspections.scheduling.create', compact('machines', 'templates', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'template_id' => 'required|exists:inspection_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,custom',
            'frequency_value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'preferred_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        InspectionSchedule::create($validated);
        
        return redirect()->route('inspections.scheduling.index')
            ->with('success', 'Jadwal inspeksi berhasil dibuat.');
    }

    public function show(InspectionSchedule $scheduling)
    {
        $scheduling->load(['machineErp.machineType', 'template.parameters', 'assignedUser', 'inspections']);
        return view('inspections.scheduling.show', compact('scheduling'));
    }

    public function edit(InspectionSchedule $scheduling)
    {
        $machines = MachineErp::with('machineType')->orderBy('idMachine')->get();
        $templates = InspectionTemplate::where('status', 'active')->with('machineType')->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        return view('inspections.scheduling.edit', compact('scheduling', 'machines', 'templates', 'users'));
    }

    public function update(Request $request, InspectionSchedule $scheduling)
    {
        $validated = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'template_id' => 'required|exists:inspection_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,custom',
            'frequency_value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'preferred_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $scheduling->update($validated);
        
        return redirect()->route('inspections.scheduling.index')
            ->with('success', 'Jadwal inspeksi berhasil diperbarui.');
    }

    public function destroy(InspectionSchedule $scheduling)
    {
        $scheduling->delete();
        return redirect()->route('inspections.scheduling.index')
            ->with('success', 'Jadwal inspeksi berhasil dihapus.');
    }

    public function updatePic(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:inspection_schedules,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $schedule = InspectionSchedule::findOrFail($validated['schedule_id']);
        $schedule->update(['assigned_to' => $validated['assigned_to']]);

        return response()->json(['success' => true, 'message' => 'PIC berhasil diperbarui']);
    }
}
