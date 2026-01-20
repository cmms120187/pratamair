<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\InspectionTemplateParameter;
use App\Models\InspectionParameterValue;
use App\Models\MachineErp;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    public function index()
    {
        $inspections = Inspection::with('machine')->orderBy('inspection_date', 'desc')->paginate(20);
        return view('inspections.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $machines = MachineErp::orderBy('idMachine')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $machineId = $request->query('machine_erp_id', null);
        $inspectionDate = $request->query('inspection_date', date('Y-m-d'));

        // Optionally pre-load template
        $template = null;
        if ($request->query('template_id')) {
            $template = InspectionTemplate::with('parameters')->find($request->query('template_id'));
        }

        return view('inspections.create', compact('machines', 'users', 'machineId', 'inspectionDate', 'template'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'inspection_date' => 'required|date',
            'template_id' => 'required|exists:inspection_templates,id',
            'performed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'parameters' => 'required|array|min:1',
            'parameters.*.template_parameter_id' => 'required|exists:inspection_template_parameters,id',
            'parameters.*.parameter_value' => 'required|numeric',
            'parameters.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create inspection
            $inspection = Inspection::create([
                'machine_erp_id' => $data['machine_erp_id'],
                'inspection_date' => $data['inspection_date'],
                'performed_by' => $request->input('performed_by', auth()->id()),
                'notes' => $request->input('notes', ''),
                'template_id' => $data['template_id'],
            ]);

            // Store parameter values
            foreach ($data['parameters'] as $paramData) {
                $templateParameter = InspectionTemplateParameter::findOrFail($paramData['template_parameter_id']);
                
                // Calculate status based on min/max values
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
            return redirect()->route('inspections.show', $inspection->id)->with('success', 'Inspeksi berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan inspeksi: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Inspection $inspection)
    {
        $inspection->load('machine', 'template', 'performedBy', 'parameterValues.templateParameter');
        return view('inspections.show', compact('inspection'));
    }

    public function edit(Inspection $inspection)
    {
        // Minimal: redirect to show for now
        return redirect()->route('inspections.show', $inspection->id);
    }

    public function update(Request $request, Inspection $inspection)
    {
        // Not implemented - keep minimal
        return redirect()->route('inspections.show', $inspection->id);
    }

    public function destroy(Inspection $inspection)
    {
        $inspection->delete();
        return redirect()->route('inspections.index')->with('success', 'Inspeksi dihapus');
    }

    public function getTemplateByMachineType(Request $request)
    {
        $machineTypeId = $request->query('machine_type_id');
        
        if (!$machineTypeId) {
            return response()->json(['template' => null], 400);
        }

        try {
            // Try to find template by machine_type_id
            // Note: This assumes InspectionTemplate model exists with machine_type_id relationship
            if (class_exists(\App\Models\InspectionTemplate::class)) {
                $template = \App\Models\InspectionTemplate::with('parameters')
                    ->where('machine_type_id', $machineTypeId)
                    ->where('status', 'active')
                    ->first();
                
                if ($template) {
                    return response()->json([
                        'template' => [
                            'id' => $template->id,
                            'name' => $template->name,
                            'description' => $template->description ?? '',
                            'parameters' => $template->parameters->map(function($param) {
                                return [
                                    'id' => $param->id,
                                    'parameter_name' => $param->parameter_name,
                                    'unit' => $param->unit ?? '',
                                    'min_value' => $param->min_value,
                                    'max_value' => $param->max_value,
                                    'sequence' => $param->sequence ?? 0,
                                    'instruction' => $param->instruction ?? '',
                                    'photo' => $param->photo ? asset('storage/' . $param->photo) : null,
                                ];
                            })->values()->all()
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Log error if needed
            \Log::error('Error fetching inspection template: ' . $e->getMessage());
        }
        
        return response()->json(['template' => null]);
    }
}
