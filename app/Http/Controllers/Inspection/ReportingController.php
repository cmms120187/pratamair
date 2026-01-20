<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionSchedule;
use App\Models\InspectionParameterValue;
use App\Models\MachineErp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with(['machine.machineType', 'template', 'performedBy', 'parameterValues.templateParameter']);
        
        // Filters
        if ($request->filled('machine_type_id')) {
            $query->whereHas('machine', function($q) use ($request) {
                $q->where('machine_type_id', $request->machine_type_id);
            });
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
        
        if ($request->filled('status')) {
            $query->whereHas('parameterValues', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        
        $inspections = $query->orderBy('inspection_date', 'desc')->paginate(20);
        
        // Statistics
        $stats = [
            'total' => Inspection::count(),
            'this_month' => Inspection::whereMonth('inspection_date', now()->month)
                ->whereYear('inspection_date', now()->year)->count(),
            'critical' => InspectionParameterValue::where('status', 'critical')->count(),
            'warning' => InspectionParameterValue::where('status', 'warning')->count(),
            'normal' => InspectionParameterValue::where('status', 'normal')->count(),
        ];
        
        $machineTypes = \App\Models\MachineType::orderBy('name')->get();
        $machines = MachineErp::orderBy('idMachine')->get();
        
        return view('inspections.reporting.index', compact('inspections', 'stats', 'machineTypes', 'machines'));
    }

    public function show($id)
    {
        $inspection = Inspection::with([
            'machine.machineType',
            'template.parameters',
            'performedBy',
            'parameterValues.templateParameter',
            'schedule'
        ])->findOrFail($id);
        
        return view('inspections.reporting.show', compact('inspection'));
    }
}
