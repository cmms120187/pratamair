<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ImageHelper;

class ModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $models = \App\Models\Model::with(['brand', 'machineType'])
            ->orderBy('name', 'asc')
            ->paginate(12);
        return view('models.index', compact('models'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = \App\Models\Brand::all();
        $machineTypes = \App\Models\MachineType::all();
        return view('models.create', compact('brands', 'machineTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:models,name',
            'brand_id' => 'required|integer|exists:brands,id',
            'type_id' => 'required|integer|exists:machine_types,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoPath = ImageHelper::convertToWebP($photo, 'models', 85);
            $validated['photo'] = $photoPath;
        }
        
        $model = new \App\Models\Model();
        $model->name = $validated['name'];
        $model->brand_id = $validated['brand_id'];
        $model->type_id = $validated['type_id'];
        if (isset($validated['photo'])) {
            $model->photo = $validated['photo'];
        }
        $model->save();
        return redirect()->route('models.index')->with('success', 'Model created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model = \App\Models\Model::findOrFail($id);
        $brands = \App\Models\Brand::all();
        $machineTypes = \App\Models\MachineType::all();
        return view('models.edit', compact('model', 'brands', 'machineTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:models,name,' . $id,
            'brand_id' => 'required|integer|exists:brands,id',
            'type_id' => 'required|integer|exists:machine_types,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);
        
        $model = \App\Models\Model::findOrFail($id);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            ImageHelper::deleteOldImage($model->photo);
            
            $photo = $request->file('photo');
            // Convert to WebP
            $photoPath = ImageHelper::convertToWebP($photo, 'models', 85);
            $validated['photo'] = $photoPath;
        } else {
            unset($validated['photo']); // Keep existing photo if not uploaded
        }
        
        $model->name = $validated['name'];
        $model->brand_id = $validated['brand_id'];
        $model->type_id = $validated['type_id'];
        if (isset($validated['photo'])) {
            $model->photo = $validated['photo'];
        }
        $model->save();
        return redirect()->route('models.index')->with('success', 'Model updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = \App\Models\Model::findOrFail($id);
        $model->delete();
        return redirect()->route('models.index')->with('success', 'Model deleted successfully.');
    }

    /**
     * Import models from machine_erp table
     * This will create models from unique model_name in machine_erp
     * Handles case-insensitive duplicates and normalizes names
     */
    public function importFromMachineErp()
    {
        try {
            // Get all model_name from machine_erp with type_name and brand_name
            $allModels = \App\Models\MachineErp::whereNotNull('model_name')
                ->where('model_name', '!=', '')
                ->whereNotNull('type_name')
                ->where('type_name', '!=', '')
                ->select('model_name', 'type_name', 'brand_name')
                ->get();
            
            $created = 0;
            $skipped = 0;
            $errors = 0;
            
            foreach ($allModels as $machineErp) {
                try {
                    $modelName = trim($machineErp->model_name);
                    $typeName = trim($machineErp->type_name);
                    $brandName = trim($machineErp->brand_name ?? '');
                    
                    if (empty($modelName) || empty($typeName)) {
                        continue;
                    }
                    
                    // Find or get machine type
                    $machineType = \App\Models\MachineType::whereRaw('LOWER(name) = ?', [strtolower($typeName)])->first();
                    if (!$machineType) {
                        $errors++;
                        continue;
                    }
                    
                    // Find or get brand
                    $brand = null;
                    if (!empty($brandName)) {
                        $brand = \App\Models\Brand::whereRaw('LOWER(name) = ?', [strtolower($brandName)])->first();
                        if (!$brand) {
                            // Create brand if not exists
                            $brand = \App\Models\Brand::create(['name' => $brandName]);
                        }
                    }
                    
                    // Check if model already exists (case-insensitive, same type and brand)
                    $existing = \App\Models\Model::where('type_id', $machineType->id)
                        ->whereRaw('LOWER(name) = ?', [strtolower($modelName)]);
                    
                    if ($brand) {
                        $existing->where('brand_id', $brand->id);
                    } else {
                        $existing->whereNull('brand_id');
                    }
                    
                    $existing = $existing->first();
                    
                    if (!$existing) {
                        \App\Models\Model::create([
                            'name' => $modelName,
                            'type_id' => $machineType->id,
                            'brand_id' => $brand ? $brand->id : null,
                        ]);
                        $created++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error('Error importing model from machine_erp: ' . $e->getMessage(), [
                        'model_name' => $machineErp->model_name,
                        'type_name' => $machineErp->type_name,
                    ]);
                }
            }
            
            $message = "Imported $created new models from machine_erp. $skipped already existed.";
            if ($errors > 0) {
                $message .= " $errors rows skipped due to errors.";
            }
            
            return redirect()->route('models.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing models from machine_erp: ' . $e->getMessage());
            return redirect()->route('models.index')->withErrors(['error' => 'Error importing models: ' . $e->getMessage()]);
        }
    }

    /**
     * Merge duplicate models (case-insensitive)
     * This will merge duplicates and update all related records
     */
    public function mergeDuplicates()
    {
        try {
            // Get all models with their relationships
            $allModels = \App\Models\Model::with(['brand', 'machineType'])->get();
            
            // Group by lowercase name, type_id, and brand_id
            $grouped = [];
            foreach ($allModels as $model) {
                $key = strtolower(trim($model->name)) . '|' . $model->type_id . '|' . ($model->brand_id ?? 'null');
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [];
                }
                $grouped[$key][] = $model;
            }
            
            $merged = 0;
            $deleted = 0;
            
            foreach ($grouped as $key => $models) {
                if (count($models) > 1) {
                    // Keep the first one (or the one with photo)
                    usort($models, function($a, $b) {
                        $aScore = $a->photo ? 10 : 0;
                        $bScore = $b->photo ? 10 : 0;
                        return $bScore <=> $aScore;
                    });
                    
                    $keep = $models[0];
                    $duplicates = array_slice($models, 1);
                    
                    // Update all related records to use the kept model
                    foreach ($duplicates as $duplicate) {
                        // Update machines if exists
                        if (\Schema::hasTable('machines')) {
                            \DB::table('machines')
                                ->where('model_id', $duplicate->id)
                                ->update(['model_id' => $keep->id]);
                        }
                        
                        // Update machine_erp if needed
                        \App\Models\MachineErp::where('model_name', $duplicate->name)
                            ->where('type_name', $keep->machineType->name ?? '')
                            ->update(['model_name' => $keep->name]);
                        
                        // Update name if kept one is uppercase but duplicate has better case
                        if (strtoupper($keep->name) === $keep->name && strtolower($duplicate->name) !== $duplicate->name) {
                            $keep->update(['name' => $duplicate->name]);
                        }
                        
                        // Delete duplicate
                        $duplicate->delete();
                        $deleted++;
                    }
                    
                    $merged++;
                }
            }
            
            $message = "Merged $merged groups of duplicates. $deleted duplicate models deleted.";
            return redirect()->route('models.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error merging duplicate models: ' . $e->getMessage());
            return redirect()->route('models.index')->withErrors(['error' => 'Error merging duplicates: ' . $e->getMessage()]);
        }
    }
}
