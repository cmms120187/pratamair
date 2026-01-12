<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\MachineErp;

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
            'parameters' => 'array',
        ]);

        $inspection = Inspection::create([
            'machine_erp_id' => $data['machine_erp_id'],
            'inspection_date' => $data['inspection_date'],
            'performed_by' => $request->input('performed_by', auth()->id()),
            'notes' => $request->input('notes', ''),
            'template_id' => $data['template_id'],
        ]);

        // TODO: store parameters as JSON or related table. For now keep minimal.

        return redirect()->route('inspections.show', $inspection->id)->with('success', 'Inspeksi disimpan');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load('machine', 'template');
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
}
