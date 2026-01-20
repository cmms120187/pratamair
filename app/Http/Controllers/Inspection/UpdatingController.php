<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionSchedule;
use App\Models\InspectionTemplate;
use App\Models\InspectionTemplateParameter;
use App\Models\InspectionParameterValue;
use App\Models\MachineErp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdatingController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with(['machine', 'template', 'performedBy', 'schedule']);
        
        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }
        
        if ($request->filled('machine_erp_id')) {
            $query->where('machine_erp_id', $request->machine_erp_id);
        }
        
        if ($request->filled('date_from')) {
            $query->where('inspection_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('inspection_date', '<=', $request->date_to);
        }
        
        $inspections = $query->orderBy('inspection_date', 'desc')->paginate(20);
        $schedules = InspectionSchedule::where('status', 'active')->with('machineErp')->orderBy('start_date', 'desc')->get();
        $machines = MachineErp::orderBy('idMachine')->get();
        
        return view('inspections.updating.index', compact('inspections', 'schedules', 'machines'));
    }

    public function create($scheduleId = null)
    {
        $machines = MachineErp::with('machineType')->orderBy('idMachine')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $schedule = null;
        $template = null;
        
        if ($scheduleId) {
            $schedule = InspectionSchedule::with(['machineErp', 'template.parameters'])->findOrFail($scheduleId);
            $template = $schedule->template;
        }
        
        $templates = InspectionTemplate::where('status', 'active')->with('machineType')->orderBy('name')->get();
        
        return view('inspections.updating.create', compact('machines', 'users', 'templates', 'schedule', 'template'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'inspection_date' => 'required|date',
            'template_id' => 'required|exists:inspection_templates,id',
            'schedule_id' => 'nullable|exists:inspection_schedules,id',
            'performed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'parameters' => 'required|array|min:1',
            'parameters.*.template_parameter_id' => 'required|exists:inspection_template_parameters,id',
            'parameters.*.parameter_value' => 'required|numeric',
            'parameters.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $inspection = Inspection::create([
                'machine_erp_id' => $data['machine_erp_id'],
                'inspection_date' => $data['inspection_date'],
                'performed_by' => $request->input('performed_by', auth()->id()),
                'notes' => $request->input('notes', ''),
                'template_id' => $data['template_id'],
                'schedule_id' => $data['schedule_id'] ?? null,
            ]);

            foreach ($data['parameters'] as $paramData) {
                $templateParameter = InspectionTemplateParameter::findOrFail($paramData['template_parameter_id']);
                $status = $templateParameter->checkValueStatus($paramData['parameter_value']);
                
                InspectionParameterValue::create([
                    'inspection_id' => $inspection->id,
                    'template_parameter_id' => $paramData['template_parameter_id'],
                    'parameter_value' => $paramData['parameter_value'],
                    'status' => $status,
                    'notes' => $paramData['notes'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('inspections.updating.index')
                ->with('success', 'Inspeksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan inspeksi: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $inspection = Inspection::with(['machine', 'template.parameters', 'parameterValues.templateParameter', 'performedBy'])->findOrFail($id);
        $machines = MachineErp::with('machineType')->orderBy('idMachine')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $templates = InspectionTemplate::where('status', 'active')->with('machineType')->orderBy('name')->get();
        
        return view('inspections.updating.edit', compact('inspection', 'machines', 'users', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $inspection = Inspection::findOrFail($id);
        
        $data = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'inspection_date' => 'required|date',
            'template_id' => 'required|exists:inspection_templates,id',
            'performed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'parameters' => 'required|array|min:1',
            'parameters.*.id' => 'nullable|exists:inspection_parameter_values,id',
            'parameters.*.template_parameter_id' => 'required|exists:inspection_template_parameters,id',
            'parameters.*.parameter_value' => 'required|numeric',
            'parameters.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $inspection->update([
                'machine_erp_id' => $data['machine_erp_id'],
                'inspection_date' => $data['inspection_date'],
                'performed_by' => $request->input('performed_by', auth()->id()),
                'notes' => $request->input('notes', ''),
                'template_id' => $data['template_id'],
            ]);

            // Update or create parameter values
            $existingIds = collect($data['parameters'])->pluck('id')->filter()->all();
            $inspection->parameterValues()->whereNotIn('id', $existingIds)->delete();

            foreach ($data['parameters'] as $paramData) {
                $templateParameter = InspectionTemplateParameter::findOrFail($paramData['template_parameter_id']);
                $status = $templateParameter->checkValueStatus($paramData['parameter_value']);
                
                if (isset($paramData['id'])) {
                    InspectionParameterValue::where('id', $paramData['id'])
                        ->update([
                            'template_parameter_id' => $paramData['template_parameter_id'],
                            'parameter_value' => $paramData['parameter_value'],
                            'status' => $status,
                            'notes' => $paramData['notes'] ?? null,
                        ]);
                } else {
                    InspectionParameterValue::create([
                        'inspection_id' => $inspection->id,
                        'template_parameter_id' => $paramData['template_parameter_id'],
                        'parameter_value' => $paramData['parameter_value'],
                        'status' => $status,
                        'notes' => $paramData['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('inspections.updating.index')
                ->with('success', 'Inspeksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui inspeksi: ' . $e->getMessage()])->withInput();
        }
    }
}
