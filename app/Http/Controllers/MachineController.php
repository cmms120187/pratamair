<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Machine::with(['plant','process','line','room','machineType','brand','model']);
        
        // Apply filters
        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }
        
        if ($request->filled('process_id')) {
            $query->where('process_id', $request->process_id);
        }
        
        if ($request->filled('line_id')) {
            $query->where('line_id', $request->line_id);
        }
        
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
        
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        
        if ($request->filled('model_id')) {
            $query->where('model_id', $request->model_id);
        }
        
        if ($request->filled('idMachine')) {
            $query->where('idMachine', 'like', '%' . $request->idMachine . '%');
        }
        
        $machines = $query->paginate(12)->withQueryString();
        
        // Get filter options
        $plants = \App\Models\Plant::orderBy('name')->get();
        $processes = \App\Models\Process::orderBy('name')->get();
        $lines = \App\Models\Line::orderBy('name')->get();
        $rooms = \App\Models\Room::orderBy('name')->get();
        $machineTypes = \App\Models\MachineType::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $models = \App\Models\Model::orderBy('name')->get();
        
        return view('machinary.machines.index', compact('machines', 'plants', 'processes', 'lines', 'rooms', 'machineTypes', 'brands', 'models'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plants = \App\Models\Plant::all();
        $processes = \App\Models\Process::all();
        
        // Get machine types from models table (distinct)
        $machineTypes = \App\Models\MachineType::whereHas('models')->orderBy('name')->get();
        
        return view('machinary.machines.create', compact('plants', 'processes', 'machineTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idMachine' => 'required|string|max:255|unique:machines,idMachine',
            'plant_id' => 'required|integer|exists:plants,id',
            'process_id' => 'required|integer|exists:processes,id',
            'line_id' => 'required|integer|exists:lines,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'type_id' => 'required|integer|exists:machine_types,id',
            'brand_id' => 'required|integer|exists:brands,id',
            'model_id' => 'required|integer|exists:models,id',
        ]);

        // Validate that the selected line belongs to the selected plant
        $line = \App\Models\Line::findOrFail($validated['line_id']);
        if ($line->plant_id != $validated['plant_id']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['line_id' => 'Line yang dipilih tidak sesuai dengan Plant yang dipilih.']);
        }

        // Validate that the selected room belongs to the selected plant and line
        $room = \App\Models\Room::findOrFail($validated['room_id']);
        if ($room->plant_id != $validated['plant_id'] || $room->line_id != $validated['line_id']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['room_id' => 'Room yang dipilih tidak sesuai dengan Plant dan Line yang dipilih.']);
        }

        // Get model and use its type_id and brand_id (model is the source of truth)
        $model = \App\Models\Model::findOrFail($validated['model_id']);
        
        // Override type_id and brand_id from model
        $validated['type_id'] = $model->type_id;
        $validated['brand_id'] = $model->brand_id;

        $machine = new \App\Models\Machine();
        $machine->fill($validated);
        $machine->save();
        return redirect()->route('machines.index')->with('success', 'Machine created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $machine = \App\Models\Machine::with([
            'plant', 
            'process', 
            'line', 
            'room', 
            'machineType.groupRelation', 
            'machineType.systems', 
            'brand', 
            'model'
        ])->findOrFail($id);
        return view('machinary.machines.show', compact('machine'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $machine = \App\Models\Machine::findOrFail($id);
        $plants = \App\Models\Plant::all();
        $processes = \App\Models\Process::all();
        
        // Get machine types from models table (distinct)
        $machineTypes = \App\Models\MachineType::whereHas('models')->orderBy('name')->get();
        
        return view('machinary.machines.edit', compact('machine', 'plants', 'processes', 'machineTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $machine = \App\Models\Machine::findOrFail($id);
        
        $validated = $request->validate([
            'idMachine' => 'required|string|max:255|unique:machines,idMachine,' . $id,
            'plant_id' => 'required|integer|exists:plants,id',
            'process_id' => 'required|integer|exists:processes,id',
            'line_id' => 'required|integer|exists:lines,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'type_id' => 'required|integer|exists:machine_types,id',
            'brand_id' => 'required|integer|exists:brands,id',
            'model_id' => 'required|integer|exists:models,id',
        ]);

        // Validate that the selected line belongs to the selected plant
        $line = \App\Models\Line::findOrFail($validated['line_id']);
        if ($line->plant_id != $validated['plant_id']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['line_id' => 'Line yang dipilih tidak sesuai dengan Plant yang dipilih.']);
        }

        // Validate that the selected room belongs to the selected plant and line
        $room = \App\Models\Room::findOrFail($validated['room_id']);
        if ($room->plant_id != $validated['plant_id'] || $room->line_id != $validated['line_id']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['room_id' => 'Room yang dipilih tidak sesuai dengan Plant dan Line yang dipilih.']);
        }

        // Get model and use its type_id and brand_id (model is the source of truth)
        $model = \App\Models\Model::findOrFail($validated['model_id']);
        
        // Override type_id and brand_id from model
        $validated['type_id'] = $model->type_id;
        $validated['brand_id'] = $model->brand_id;

        $machine->fill($validated);
        $machine->save();
        return redirect()->route('machines.index')->with('success', 'Machine updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $machine = \App\Models\Machine::findOrFail($id);
        $machine->delete();
        return redirect()->route('machines.index')->with('success', 'Machine deleted successfully.');
    }

    /**
     * Get brands by type (AJAX)
     */
    public function getBrandsByType(Request $request)
    {
        try {
            $typeId = $request->get('type_id') ?? $request->input('type_id');
            
            if (!$typeId) {
                return response()->json([], 200, [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-cache'
                ]);
            }
            
            if (!is_numeric($typeId)) {
                return response()->json([
                    'error' => 'Invalid type_id'
                ], 400, ['Content-Type' => 'application/json']);
            }
            
            // Get distinct brands from models that have this type_id
            $brands = \App\Models\Brand::whereHas('models', function($query) use ($typeId) {
                $query->where('type_id', (int)$typeId);
            })->orderBy('name', 'asc')->get();
            
            $data = $brands->map(function($brand) {
                return [
                    'id' => (int)$brand->id,
                    'name' => (string)$brand->name
                ];
            })->values()->toArray();
            
            return response()->json($data, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'no-cache'
            ]);
                
        } catch (\Exception $e) {
            \Log::error('Error in getBrandsByType: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'type_id' => $request->get('type_id')
            ]);
            
            return response()->json([
                'error' => 'Error fetching brands: ' . $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);
        }
    }

    /**
     * Get lines by plant (AJAX) - from rooms table
     */
    public function getLinesByPlant(Request $request)
    {
        try {
            $plantId = $request->get('plant_id') ?? $request->input('plant_id');
            
            if (!$plantId) {
                return response()->json([], 200, [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-cache'
                ]);
            }
            
            if (!is_numeric($plantId)) {
                return response()->json([
                    'error' => 'Invalid plant_id'
                ], 400, ['Content-Type' => 'application/json']);
            }
            
            // Get distinct lines from rooms table based on plant_id
            $lines = \App\Models\Line::whereHas('rooms', function($query) use ($plantId) {
                $query->where('plant_id', (int)$plantId);
            })->orWhere('plant_id', (int)$plantId)
            ->distinct()
            ->orderBy('name', 'asc')
            ->get();
            
            $data = $lines->map(function($line) {
                return [
                    'id' => (int)$line->id,
                    'name' => (string)$line->name
                ];
            })->values()->toArray();
            
            return response()->json($data, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'no-cache'
            ]);
                
        } catch (\Exception $e) {
            \Log::error('Error in getLinesByPlant: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'plant_id' => $request->get('plant_id')
            ]);
            
            return response()->json([
                'error' => 'Error fetching lines: ' . $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);
        }
    }

    /**
     * Get rooms by plant and line (AJAX)
     */
    public function getRoomsByPlantAndLine(Request $request)
    {
        try {
            $plantId = $request->get('plant_id') ?? $request->input('plant_id');
            $lineId = $request->get('line_id') ?? $request->input('line_id');
            
            if (!$plantId || !$lineId) {
                return response()->json([], 200, [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-cache'
                ]);
            }
            
            if (!is_numeric($plantId) || !is_numeric($lineId)) {
                return response()->json([
                    'error' => 'Invalid plant_id or line_id'
                ], 400, ['Content-Type' => 'application/json']);
            }
            
            $rooms = \App\Models\Room::where('plant_id', (int)$plantId)
                ->where('line_id', (int)$lineId)
                ->orderBy('name', 'asc')
                ->get();
            
            $data = $rooms->map(function($room) {
                return [
                    'id' => (int)$room->id,
                    'name' => (string)$room->name
                ];
            })->values()->toArray();
            
            return response()->json($data, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'no-cache'
            ]);
                
        } catch (\Exception $e) {
            \Log::error('Error in getRoomsByPlantAndLine: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'plant_id' => $request->get('plant_id'),
                'line_id' => $request->get('line_id')
            ]);
            
            return response()->json([
                'error' => 'Error fetching rooms: ' . $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);
        }
    }

    /**
     * Get models by type and brand (AJAX)
     */
    public function getModelsByTypeAndBrand(Request $request)
    {
        try {
            $typeId = $request->get('type_id') ?? $request->input('type_id');
            $brandId = $request->get('brand_id') ?? $request->input('brand_id');
            
            if (!$typeId || !$brandId) {
                return response()->json([], 200, [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-cache'
                ]);
            }
            
            if (!is_numeric($typeId) || !is_numeric($brandId)) {
                return response()->json([
                    'error' => 'Invalid type_id or brand_id'
                ], 400, ['Content-Type' => 'application/json']);
            }
            
            $models = \App\Models\Model::where('type_id', (int)$typeId)
                ->where('brand_id', (int)$brandId)
                ->orderBy('name', 'asc')
                ->get();
            
            $data = $models->map(function($model) {
                return [
                    'id' => (int)$model->id,
                    'name' => (string)$model->name,
                    'type_id' => (int)$model->type_id,
                    'brand_id' => (int)$model->brand_id
                ];
            })->values()->toArray();
            
            return response()->json($data, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'no-cache'
            ]);
                
        } catch (\Exception $e) {
            \Log::error('Error in getModelsByTypeAndBrand: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'type_id' => $request->get('type_id'),
                'brand_id' => $request->get('brand_id')
            ]);
            
            return response()->json([
                'error' => 'Error fetching models: ' . $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);
        }
    }
}
