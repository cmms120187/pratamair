<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InspectionTemplate;
use App\Models\InspectionTemplateParameter;
use App\Models\MachineType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InspectionTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = InspectionTemplate::with(['machineType', 'parameters']);
        
        // Filter by machine_type_id
        if ($request->filled('machine_type_id')) {
            $query->where('machine_type_id', $request->machine_type_id);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $templates = $query->orderBy('name', 'asc')->paginate(12);
        $machineTypes = MachineType::orderBy('name', 'asc')->get();
        
        return view('inspection-templates.index', compact('templates', 'machineTypes'));
    }

    public function create(Request $request)
    {
        $machineTypes = MachineType::orderBy('name', 'asc')->get();
        return view('inspection-templates.create', compact('machineTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_type_id' => 'required|exists:machine_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'frequency' => 'required|in:daily,weekly,monthly,custom',
            'parameters' => 'required|array|min:1',
            'parameters.*.parameter_name' => 'required|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.min_value' => 'nullable|numeric',
            'parameters.*.max_value' => 'nullable|numeric',
            'parameters.*.sequence' => 'nullable|integer|min:0',
            'parameters.*.instruction' => 'nullable|string',
            'parameters.*.photo' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Create template
            $template = InspectionTemplate::create([
                'machine_type_id' => $validated['machine_type_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'frequency' => $validated['frequency'],
            ]);

            // Create parameters
            foreach ($validated['parameters'] as $index => $paramData) {
                $photoPath = null;
                
                // Handle photo upload if provided
                if ($request->hasFile("parameters.{$index}.photo")) {
                    $photo = $request->file("parameters.{$index}.photo");
                    $photoPath = $photo->store('inspection-parameters', 'public');
                }
                
                InspectionTemplateParameter::create([
                    'inspection_template_id' => $template->id,
                    'parameter_name' => $paramData['parameter_name'],
                    'unit' => $paramData['unit'] ?? null,
                    'min_value' => isset($paramData['min_value']) && $paramData['min_value'] !== '' ? $paramData['min_value'] : null,
                    'max_value' => isset($paramData['max_value']) && $paramData['max_value'] !== '' ? $paramData['max_value'] : null,
                    'sequence' => $paramData['sequence'] ?? $index,
                    'instruction' => $paramData['instruction'] ?? null,
                    'photo' => $photoPath,
                ]);
            }

            DB::commit();
            return redirect()->route('inspection-templates.index')->with('success', 'Template inspeksi berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat template: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $template = InspectionTemplate::with(['machineType', 'parameters'])->findOrFail($id);
        return view('inspection-templates.show', compact('template'));
    }

    public function edit($id)
    {
        $template = InspectionTemplate::with('parameters')->findOrFail($id);
        $machineTypes = MachineType::orderBy('name', 'asc')->get();
        return view('inspection-templates.edit', compact('template', 'machineTypes'));
    }

    public function update(Request $request, $id)
    {
        $template = InspectionTemplate::findOrFail($id);
        
        $validated = $request->validate([
            'machine_type_id' => 'required|exists:machine_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'frequency' => 'required|in:daily,weekly,monthly,custom',
            'parameters' => 'required|array|min:1',
            'parameters.*.id' => 'nullable|exists:inspection_template_parameters,id',
            'parameters.*.parameter_name' => 'required|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.min_value' => 'nullable|numeric',
            'parameters.*.max_value' => 'nullable|numeric',
            'parameters.*.sequence' => 'nullable|integer|min:0',
            'parameters.*.instruction' => 'nullable|string',
            'parameters.*.photo' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Update template
            $template->update([
                'machine_type_id' => $validated['machine_type_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'frequency' => $validated['frequency'],
            ]);

            // Get existing parameter IDs
            $existingParamIds = collect($validated['parameters'])->pluck('id')->filter()->toArray();
            
            // Delete parameters that are not in the request
            InspectionTemplateParameter::where('inspection_template_id', $template->id)
                ->whereNotIn('id', $existingParamIds)
                ->delete();

            // Update or create parameters
            foreach ($validated['parameters'] as $index => $paramData) {
                $photoPath = null;
                
                // Handle photo upload if provided
                if ($request->hasFile("parameters.{$index}.photo")) {
                    $photo = $request->file("parameters.{$index}.photo");
                    $photoPath = $photo->store('inspection-parameters', 'public');
                } else if (isset($paramData['id'])) {
                    // Keep existing photo if not updated
                    $existingParam = InspectionTemplateParameter::find($paramData['id']);
                    if ($existingParam && $existingParam->photo) {
                        $photoPath = $existingParam->photo;
                    }
                }
                
                if (isset($paramData['id']) && $paramData['id']) {
                    // Update existing parameter
                    InspectionTemplateParameter::where('id', $paramData['id'])
                        ->update([
                            'parameter_name' => $paramData['parameter_name'],
                            'unit' => $paramData['unit'] ?? null,
                            'min_value' => isset($paramData['min_value']) && $paramData['min_value'] !== '' ? $paramData['min_value'] : null,
                            'max_value' => isset($paramData['max_value']) && $paramData['max_value'] !== '' ? $paramData['max_value'] : null,
                            'sequence' => $paramData['sequence'] ?? $index,
                            'instruction' => $paramData['instruction'] ?? null,
                            'photo' => $photoPath,
                        ]);
                } else {
                    // Create new parameter
                    InspectionTemplateParameter::create([
                        'inspection_template_id' => $template->id,
                        'parameter_name' => $paramData['parameter_name'],
                        'unit' => $paramData['unit'] ?? null,
                        'min_value' => isset($paramData['min_value']) && $paramData['min_value'] !== '' ? $paramData['min_value'] : null,
                        'max_value' => isset($paramData['max_value']) && $paramData['max_value'] !== '' ? $paramData['max_value'] : null,
                        'sequence' => $paramData['sequence'] ?? $index,
                        'instruction' => $paramData['instruction'] ?? null,
                        'photo' => $photoPath,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('inspection-templates.index')->with('success', 'Template inspeksi berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui template: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $template = InspectionTemplate::findOrFail($id);
        
        // Check if template is used in inspections
        if ($template->inspections()->count() > 0) {
            return redirect()->route('inspection-templates.index')
                ->with('error', 'Template tidak dapat dihapus karena sudah digunakan dalam inspeksi');
        }
        
        DB::beginTransaction();
        try {
            // Delete parameters (cascade should handle this, but doing explicitly)
            InspectionTemplateParameter::where('inspection_template_id', $template->id)->delete();
            
            // Delete template
            $template->delete();
            
            DB::commit();
            return redirect()->route('inspection-templates.index')->with('success', 'Template inspeksi berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('inspection-templates.index')
                ->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }
}
