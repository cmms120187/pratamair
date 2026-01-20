<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineErp;
use App\Models\RoomErp;
use App\Models\Group;
use App\Models\MachineType;
use App\Models\Model as ModelModel;
use App\Models\Brand;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helpers\ImageHelper;

class MachineErpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MachineErp::query();
        
        // Apply filters
        if ($request->filled('filter_id_machine')) {
            $query->where('idMachine', 'like', '%' . $request->filter_id_machine . '%');
        }
        
        if ($request->filled('filter_kode_room')) {
            $query->where('kode_room', 'like', '%' . $request->filter_kode_room . '%');
        }
        
        if ($request->filled('filter_plant_name')) {
            $query->where('plant_name', 'like', '%' . $request->filter_plant_name . '%');
        }
        
        if ($request->filled('filter_process_name')) {
            $query->where('process_name', 'like', '%' . $request->filter_process_name . '%');
        }
        
        if ($request->filled('filter_line_name')) {
            $query->where('line_name', 'like', '%' . $request->filter_line_name . '%');
        }
        
        if ($request->filled('filter_room_name')) {
            $query->where('room_name', 'like', '%' . $request->filter_room_name . '%');
        }
        
        if ($request->filled('filter_type_name')) {
            $query->where('type_name', 'like', '%' . $request->filter_type_name . '%');
        }
        
        if ($request->filled('filter_brand_name')) {
            $query->where('brand_name', 'like', '%' . $request->filter_brand_name . '%');
        }
        
        if ($request->filled('filter_model_name')) {
            $query->where('model_name', 'like', '%' . $request->filter_model_name . '%');
        }
        
        // Get unique values for filter dropdowns
        $plantNames = MachineErp::whereNotNull('plant_name')->distinct()->orderBy('plant_name')->pluck('plant_name')->toArray();
        $processNames = MachineErp::whereNotNull('process_name')->distinct()->orderBy('process_name')->pluck('process_name')->toArray();
        $lineNames = MachineErp::whereNotNull('line_name')->distinct()->orderBy('line_name')->pluck('line_name')->toArray();
        $roomNames = MachineErp::whereNotNull('room_name')->distinct()->orderBy('room_name')->pluck('room_name')->toArray();
        $typeNames = MachineErp::whereNotNull('type_name')->distinct()->orderBy('type_name')->pluck('type_name')->toArray();
        $brandNames = MachineErp::whereNotNull('brand_name')->distinct()->orderBy('brand_name')->pluck('brand_name')->toArray();
        $modelNames = MachineErp::whereNotNull('model_name')->distinct()->orderBy('model_name')->pluck('model_name')->toArray();
        
        $machineErps = $query->orderBy('idMachine', 'asc')->paginate(15)->withQueryString();
        
        return view('machine_erp.index', compact('machineErps', 'plantNames', 'processNames', 'lineNames', 'roomNames', 'typeNames', 'brandNames', 'modelNames'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all groups with their systems for dropdown
        $groups = Group::with('systems')->orderBy('name', 'asc')->get();
        
        // Map groups data for JavaScript
        $groupsData = [];
        foreach ($groups as $group) {
            $systems = [];
            foreach ($group->systems as $system) {
                $systems[] = [
                    'id' => (string)$system->id,
                    'nama_sistem' => $system->nama_sistem ?? '',
                    'deskripsi' => $system->deskripsi ?? '',
                ];
            }
            $groupsData[] = [
                'id' => (string)$group->id,
                'name' => $group->name ?? '',
                'systems' => $systems,
            ];
        }
        
        // Get all models with relationships for dropdown (instead of machine types)
        $models = ModelModel::with(['machineType.groupRelation', 'machineType.systems', 'brand'])->orderBy('name', 'asc')->get();
        
        // Map models data for JavaScript
        $modelsData = [];
        foreach ($models as $model) {
            $machineType = $model->machineType;
            $systems = [];
            if ($machineType) {
                foreach ($machineType->systems as $system) {
                    $systems[] = [
                        'id' => (string)$system->id,
                        'nama_sistem' => $system->nama_sistem ?? '',
                        'deskripsi' => $system->deskripsi ?? '',
                    ];
                }
            }
            
            $modelsData[] = [
                'id' => (string)$model->id,
                'name' => $model->name ?? '',
                'type_id' => $machineType ? (string)$machineType->id : null,
                'type_name' => $machineType ? $machineType->name : '',
                'brand_id' => $model->brand_id ? (string)$model->brand_id : null,
                'brand_name' => $model->brand ? $model->brand->name : '',
                'group_id' => $machineType && $machineType->group_id ? (string)$machineType->group_id : null,
                'group_name' => $machineType && $machineType->groupRelation ? $machineType->groupRelation->name : '',
                'systems' => $systems,
            ];
        }
        
        // Get Room ERPs for dropdown
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        
        return view('machine_erp.create', compact('groups', 'groupsData', 'models', 'modelsData', 'roomErps'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idMachine' => 'required|string|max:255|unique:machine_erp,idMachine',
            'plant_name' => 'nullable|string|max:255',
            'process_name' => 'nullable|string|max:255',
            'line_name' => 'nullable|string|max:255',
            'room_name' => 'nullable|string|max:255',
            'type_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'tahun_production' => 'nullable|integer',
            'no_document' => 'nullable|string|max:255',
            'photo' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:Running,Standby,Damage,Destroy,Other',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        // Handle photo upload - Save to database using Photo model
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoModel = ImageHelper::saveToDatabase(
                $photo,
                'machine-erp',
                'machine_erp',
                null, // Will be set after machineErp is created
                'Photo for Machine ERP'
            );
            if ($photoModel) {
                $validated['photo_id'] = $photoModel->id;
            }
        } else {
            unset($validated['photo']); // Remove photo from validated if not uploaded
        }
        
        // Auto-relate with machine_types, group, and system
        $machineTypeId = null;
        if (!empty($validated['type_name'])) {
            // Find or create machine type based on type_name
            $machineType = MachineType::firstOrCreate(
                ['name' => $validated['type_name']],
                [
                    'brand' => $validated['brand_name'] ?? null,
                    'model' => $validated['model_name'] ?? null,
                ]
            );
            $machineTypeId = $machineType->id;
            
            // If group_id is provided, update machine type with group and systems
            if ($request->filled('group_id')) {
                $group = Group::with('systems')->find($request->group_id);
                if ($group) {
                    // Update machine type with group
                    $machineType->update(['group_id' => $group->id]);
                    
                    // Sync systems from group
                    if ($group->systems->isNotEmpty()) {
                        $machineType->systems()->sync($group->systems->pluck('id')->toArray());
                    }
                }
            }
            
            // Auto-create model if model_name is provided
            if (!empty($validated['model_name']) && $machineTypeId) {
                // Try to find brand first if brand_name is provided
                $brandId = null;
                if (!empty($validated['brand_name'])) {
                    $brand = \App\Models\Brand::firstOrCreate(['name' => $validated['brand_name']]);
                    $brandId = $brand->id;
                }
                
                // Create or find model
                ModelModel::firstOrCreate(
                    [
                        'name' => $validated['model_name'],
                        'type_id' => $machineTypeId,
                    ],
                    [
                        'brand_id' => $brandId,
                    ]
                );
            }
        }
        
        $validated['machine_type_id'] = $machineTypeId;
        unset($validated['group_id']); // Remove group_id from validated as it's not a column in machine_erp
        
        $machineErp = MachineErp::create($validated);
        
        // Update photo related_id after machineErp is created
        if (isset($validated['photo_id'])) {
            $photoModel = \App\Models\Photo::find($validated['photo_id']);
            if ($photoModel) {
                $photoModel->update([
                    'related_id' => $machineErp->id
                ]);
            }
        }
        
        return redirect()->route('machine-erp.index')->with('success', 'Machine ERP created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $machineErp = MachineErp::with([
            'machineType.groupRelation', 
            'machineType.systems', 
            'machineType.models',
            'machineType.photoModel',
            'photoModel'
        ])->findOrFail($id);
        
        // Get model photo if available (optional, for future use)
        $modelPhoto = null;
        if ($machineErp->type_name && $machineErp->model_name && $machineErp->machineType) {
            $model = \App\Models\Model::where('type_id', $machineErp->machineType->id)
                ->where('name', $machineErp->model_name)
                ->first();
            
            if ($model && $model->photo) {
                $modelPhoto = $model->photo;
            }
        }
        
        // Get Last Maintenance Information
        $lastMaintenance = $this->getLastMaintenanceInfo($machineErp->idMachine, $machineErp->id);
        
        // Get Sparepart Usage (sparepart yang sudah digunakan dari downtime_erp2)
        $sparepartUsage = [];
        $usedPartIds = []; // Track PartErp IDs yang sudah ditambahkan
        $usedParts = \App\Models\DowntimeErp2::where('idMachine', $machineErp->idMachine)
            ->whereNotNull('Part')
            ->where('Part', '!=', '')
            ->distinct()
            ->pluck('Part')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        
        if (!empty($usedParts)) {
            // Cari sparepart berdasarkan nama dari field 'Part'
            foreach ($usedParts as $partName) {
                $partName = trim($partName);
                if (empty($partName)) {
                    continue;
                }
                
                // Cari di PartErp berdasarkan nama atau part_number
                $part = \App\Models\PartErp::where(function($query) use ($partName) {
                    $query->where('name', 'like', '%' . $partName . '%')
                          ->orWhere('part_number', 'like', '%' . $partName . '%');
                })->first();
                
                if ($part && !in_array($part->id, $usedPartIds)) {
                    $sparepartUsage[] = $part;
                    $usedPartIds[] = $part->id;
                } elseif (!$part) {
                    // Jika tidak ditemukan di PartErp, simpan sebagai string untuk ditampilkan
                    // Cek apakah sudah ada dengan nama yang sama (case-insensitive)
                    $exists = false;
                    foreach ($sparepartUsage as $existingPart) {
                        if (is_array($existingPart) && isset($existingPart['is_string']) && 
                            strtolower($existingPart['name']) === strtolower($partName)) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    if (!$exists) {
                        $sparepartUsage[] = [
                            'name' => $partName,
                            'part_number' => null,
                            'stock' => null,
                            'unit' => null,
                            'minimum_stock' => null,
                            'is_string' => true, // Flag untuk menandai ini adalah string, bukan model
                        ];
                    }
                }
            }
        }
        
        return view('machine_erp.show', compact('machineErp', 'modelPhoto', 'lastMaintenance', 'sparepartUsage'));
    }

    /**
     * Get last maintenance information for a machine
     */
    private function getLastMaintenanceInfo(string $idMachine, int $machineErpId)
    {
        $lastMaintenance = [
            'downtime' => null,
            'preventive' => null,
            'predictive' => null,
            'checklist' => null,
        ];

        // Last Downtime (from downtime_erp2)
        $lastDowntime = \App\Models\DowntimeErp2::where('idMachine', $idMachine)
            ->whereNotNull('date')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastDowntime) {
            // Convert date to Carbon instance if it's a string
            $downtimeDate = $lastDowntime->date;
            if (is_string($downtimeDate)) {
                $downtimeDate = \Carbon\Carbon::parse($downtimeDate);
            }
            
            $lastMaintenance['downtime'] = [
                'date' => $downtimeDate,
                'problem' => $lastDowntime->problemDowntime,
                'action' => $lastDowntime->actionDowtime,
                'type' => 'Downtime',
            ];
        }

        // Last Preventive Maintenance (from preventive_maintenance_executions)
        $lastPreventive = \App\Models\PreventiveMaintenanceExecution::with('schedule', 'performedBy')
            ->whereHas('schedule', function($query) use ($machineErpId) {
                $query->where('machine_erp_id', $machineErpId);
            })
            ->where('status', 'completed')
            ->whereNotNull('actual_end_time')
            ->orderBy('actual_end_time', 'desc')
            ->first();
        
        if ($lastPreventive) {
            $lastMaintenance['preventive'] = [
                'date' => $lastPreventive->actual_end_time,
                'title' => $lastPreventive->schedule->title ?? 'Preventive Maintenance',
                'performed_by' => $lastPreventive->performedBy->name ?? null,
                'type' => 'Preventive Maintenance',
            ];
        }

        // Last Predictive Maintenance (from predictive_maintenance_executions)
        $lastPredictive = \App\Models\PredictiveMaintenanceExecution::with('schedule', 'performedBy')
            ->whereHas('schedule', function($query) use ($machineErpId) {
                $query->where('machine_erp_id', $machineErpId);
            })
            ->where('status', 'completed')
            ->whereNotNull('actual_end_time')
            ->orderBy('actual_end_time', 'desc')
            ->first();
        
        if ($lastPredictive) {
            $lastMaintenance['predictive'] = [
                'date' => $lastPredictive->actual_end_time,
                'title' => $lastPredictive->schedule->title ?? 'Predictive Maintenance',
                'performed_by' => $lastPredictive->performedBy->name ?? null,
                'measurement_status' => $lastPredictive->measurement_status,
                'type' => 'Predictive Maintenance',
            ];
        }

        // Last Checklist/Inspection (from inspections) - Skip if table doesn't exist or model not available
        try {
            // Check if Inspection class exists and table exists
            if (class_exists('App\\Models\\Inspection') && \Schema::hasTable('inspections')) {
                $lastInspection = \App\Models\Inspection::with('performedBy')
                    ->where('machine_erp_id', $machineErpId)
                    ->whereNotNull('inspection_date')
                    ->orderBy('inspection_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastInspection) {
                    // Try to get template name if available
                    $templateName = 'Inspection';
                    try {
                        // Try to access template_id directly without loading relationship
                        if ($lastInspection->template_id && class_exists('App\\Models\\InspectionTemplate')) {
                            $template = \App\Models\InspectionTemplate::find($lastInspection->template_id);
                            if ($template && $template->name) {
                                $templateName = $template->name;
                            }
                        }
                    } catch (\Exception $e) {
                        // Template class/model not available, use default name
                    }
                    
                    // Convert inspection_date to Carbon instance if it's a string
                    $inspectionDate = $lastInspection->inspection_date;
                    if (is_string($inspectionDate)) {
                        $inspectionDate = \Carbon\Carbon::parse($inspectionDate);
                    } elseif (!$inspectionDate instanceof \Carbon\Carbon) {
                        $inspectionDate = \Carbon\Carbon::parse($inspectionDate);
                    }
                    
                    $lastMaintenance['checklist'] = [
                        'date' => $inspectionDate,
                        'template' => $templateName,
                        'performed_by' => $lastInspection->performedBy->name ?? null,
                        'type' => 'Checklist/Inspection',
                    ];
                }
            }
        } catch (\Exception $e) {
            // Inspection table/model not available, skip silently
            \Log::debug('Inspection model/table not available, skipping: ' . $e->getMessage());
        }

        return $lastMaintenance;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $machineErp = MachineErp::findOrFail($id);
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        $page = $request->query('page', 1);
        
        // Get all groups with their systems for dropdown
        $groups = Group::with('systems')->orderBy('name', 'asc')->get();
        
        // Map groups data for JavaScript
        $groupsData = [];
        foreach ($groups as $group) {
            $systems = [];
            foreach ($group->systems as $system) {
                $systems[] = [
                    'id' => (string)$system->id,
                    'nama_sistem' => $system->nama_sistem ?? '',
                    'deskripsi' => $system->deskripsi ?? '',
                ];
            }
            $groupsData[] = [
                'id' => (string)$group->id,
                'name' => $group->name ?? '',
                'systems' => $systems,
            ];
        }
        
        // Get all models with relationships for dropdown (instead of machine types)
        $models = ModelModel::with(['machineType.groupRelation', 'machineType.systems', 'brand'])->orderBy('name', 'asc')->get();
        
        // Map models data for JavaScript
        $modelsData = [];
        foreach ($models as $model) {
            $machineType = $model->machineType;
            $systems = [];
            if ($machineType) {
                foreach ($machineType->systems as $system) {
                    $systems[] = [
                        'id' => (string)$system->id,
                        'nama_sistem' => $system->nama_sistem ?? '',
                        'deskripsi' => $system->deskripsi ?? '',
                    ];
                }
            }
            
            $modelsData[] = [
                'id' => (string)$model->id,
                'name' => $model->name ?? '',
                'type_id' => $machineType ? (string)$machineType->id : null,
                'type_name' => $machineType ? $machineType->name : '',
                'brand_id' => $model->brand_id ? (string)$model->brand_id : null,
                'brand_name' => $model->brand ? $model->brand->name : '',
                'group_id' => $machineType && $machineType->group_id ? (string)$machineType->group_id : null,
                'group_name' => $machineType && $machineType->groupRelation ? $machineType->groupRelation->name : '',
                'systems' => $systems,
            ];
        }
        
        return view('machine_erp.edit', compact('machineErp', 'roomErps', 'page', 'groups', 'groupsData', 'models', 'modelsData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'idMachine' => 'required|string|max:255|unique:machine_erp,idMachine,' . $id,
            'plant_name' => 'nullable|string|max:255',
            'process_name' => 'nullable|string|max:255',
            'line_name' => 'nullable|string|max:255',
            'room_name' => 'nullable|string|max:255',
            'type_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'tahun_production' => 'nullable|integer',
            'no_document' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            'status' => 'nullable|string|in:Running,Standby,Damage,Destroy,Other',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $machineErp = MachineErp::findOrFail($id);
        
        // Handle photo upload - Save to database using Photo model
        if ($request->hasFile('photo')) {
            // Delete old photo from database if exists
            if ($machineErp->photo_id) {
                ImageHelper::deletePhotoFromDatabase($machineErp->photo_id);
            } else if ($machineErp->photo) {
                // Fallback: delete old path-based photo
                ImageHelper::deleteOldImage($machineErp->photo);
            }
            
            $photo = $request->file('photo');
            $photoModel = ImageHelper::saveToDatabase(
                $photo,
                'machine-erp',
                'machine_erp',
                $machineErp->id,
                'Photo for Machine ERP'
            );
            if ($photoModel) {
                $validated['photo_id'] = $photoModel->id;
                $validated['photo'] = null; // Clear legacy path
            }
        } else {
            unset($validated['photo']); // Keep existing photo if not uploaded
        }

        // Auto-relate with machine_types, group, and system
        $machineTypeId = null;
        if (!empty($validated['type_name'])) {
            // Find or create machine type based on type_name
            $machineType = MachineType::firstOrCreate(
                ['name' => $validated['type_name']],
                [
                    'brand' => $validated['brand_name'] ?? null,
                    'model' => $validated['model_name'] ?? null,
                ]
            );
            $machineTypeId = $machineType->id;
            
            // If group_id is provided, update machine type with group and systems
            if ($request->filled('group_id')) {
                $group = Group::with('systems')->find($request->group_id);
                if ($group) {
                    // Update machine type with group
                    $machineType->update(['group_id' => $group->id]);
                    
                    // Sync systems from group
                    if ($group->systems->isNotEmpty()) {
                        $machineType->systems()->sync($group->systems->pluck('id')->toArray());
                    }
                }
            }
            
            // Auto-create model if model_name is provided
            if (!empty($validated['model_name']) && $machineTypeId) {
                // Try to find brand first if brand_name is provided
                $brandId = null;
                if (!empty($validated['brand_name'])) {
                    $brand = \App\Models\Brand::firstOrCreate(['name' => $validated['brand_name']]);
                    $brandId = $brand->id;
                }
                
                // Create or find model
                ModelModel::firstOrCreate(
                    [
                        'name' => $validated['model_name'],
                        'type_id' => $machineTypeId,
                    ],
                    [
                        'brand_id' => $brandId,
                    ]
                );
            }
        }
        
        $validated['machine_type_id'] = $machineTypeId;
        unset($validated['group_id']); // Remove group_id from validated as it's not a column in machine_erp

        // Update kode_room based on new location (plant_name, process_name, line_name, room_name)
        if (isset($validated['plant_name']) && isset($validated['process_name']) && 
            isset($validated['line_name']) && isset($validated['room_name'])) {
            
            // Find RoomERP that matches the new location
            $roomErp = RoomErp::where('plant_name', $validated['plant_name'])
                ->where('process_name', $validated['process_name'])
                ->where('line_name', $validated['line_name'])
                ->where('name', $validated['room_name'])
                ->first();
            
            // If RoomERP found and has kode_room, update kode_room
            if ($roomErp && $roomErp->kode_room) {
                $validated['kode_room'] = $roomErp->kode_room;
            } else {
                // If no matching RoomERP found, clear kode_room
                $validated['kode_room'] = null;
            }
        }
        
        $machineErp->update($validated);
        
        // Redirect back to the same page if page parameter exists
        $page = $request->input('page');
        if ($page) {
            return redirect()->route('machine-erp.index', ['page' => $page])->with('success', 'Machine ERP updated successfully.');
        }
        
        return redirect()->route('machine-erp.index')->with('success', 'Machine ERP updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $machineErp = MachineErp::findOrFail($id);
        $machineErp->delete();
        return redirect()->route('machine-erp.index')->with('success', 'Machine ERP deleted successfully.');
    }

    /**
     * Upload Excel file and import data
     */
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get header row (first row)
            $header = [];
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            // Read header from row 1
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cellValue = $worksheet->getCell($columnLetter . '1')->getValue();
                $header[] = trim($cellValue ?? '');
            }
            
            if (empty($header) || count($header) < 1) {
                return back()->withErrors(['excel_file' => 'Invalid Excel format. Please check the file format.']);
            }
            
            $rowCount = 0;
            $errorCount = 0;
            $duplicateCount = 0;
            $highestRow = $worksheet->getHighestRow();
            
            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    $rowData = [];
                    
                    // Read data from current row
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $cellValue = $worksheet->getCell($columnLetter . $row)->getValue();
                        $rowData[] = trim($cellValue ?? '');
                    }
                    
                    // Skip empty rows
                    if (empty(array_filter($rowData))) {
                        continue;
                    }
                    
                    if (count($rowData) !== count($header)) {
                        $errorCount++;
                        continue;
                    }
                    
                    $data = array_combine($header, $rowData);
                    
                    // Read kode_room from Excel
                    $kodeRoom = !empty(trim($data['kode_room'] ?? $data['Kode Room'] ?? $data['kodeRoom'] ?? '')) 
                        ? trim($data['kode_room'] ?? $data['Kode Room'] ?? $data['kodeRoom'] ?? '') 
                        : null;
                    
                    // If kode_room is provided, lookup RoomERP to get plant, process, line, and room_name
                    $plant = null;
                    $process = null;
                    $line = null;
                    $roomName = null;
                    
                    if ($kodeRoom) {
                        $roomErp = RoomErp::where('kode_room', $kodeRoom)->first();
                        if ($roomErp) {
                            $plant = $roomErp->plant_name;
                            $process = $roomErp->process_name;
                            $line = $roomErp->line_name;
                            $roomName = $roomErp->name;
                        }
                    }
                    
                    // If not found from kode_room, use values from Excel
                    if (!$plant) {
                        $plant = trim($data['plant_name'] ?? $data['Plant Name'] ?? $data['plantName'] ?? '') ?: null;
                    }
                    if (!$process) {
                        $process = trim($data['process_name'] ?? $data['Process Name'] ?? $data['processName'] ?? '') ?: null;
                    }
                    if (!$line) {
                        $line = trim($data['line_name'] ?? $data['Line Name'] ?? $data['lineName'] ?? '') ?: null;
                    }
                    if (!$roomName) {
                        $roomName = trim($data['room_name'] ?? $data['Room Name'] ?? $data['roomName'] ?? '') ?: null;
                    }
                    
                    // Map Excel columns to database columns
                    $machineData = [
                        'idMachine' => trim($data['idMachine'] ?? $data['ID Machine'] ?? $data['id_machine'] ?? ''),
                        'kode_room' => $kodeRoom,
                        'plant_name' => $plant,
                        'process_name' => $process,
                        'line_name' => $line,
                        'room_name' => $roomName,
                        'type_name' => trim($data['type_name'] ?? $data['Type Name'] ?? $data['typeName'] ?? '') ?: null,
                        'brand_name' => trim($data['brand_name'] ?? $data['Brand Name'] ?? $data['brandName'] ?? '') ?: null,
                        'model_name' => trim($data['model_name'] ?? $data['Model Name'] ?? $data['modelName'] ?? '') ?: null,
                        'serial_number' => trim($data['serial_number'] ?? $data['Serial Number'] ?? $data['serialNumber'] ?? '') ?: null,
                        'tahun_production' => !empty(trim($data['tahun_production'] ?? $data['Tahun Production'] ?? $data['tahunProduction'] ?? '')) ? (int)trim($data['tahun_production'] ?? $data['Tahun Production'] ?? $data['tahunProduction'] ?? '') : null,
                        'no_document' => trim($data['no_document'] ?? $data['No Document'] ?? $data['noDocument'] ?? '') ?: null,
                        'status' => trim($data['status'] ?? $data['Status'] ?? '') ?: null,
                        'photo' => trim($data['photo'] ?? $data['Photo'] ?? '') ?: null,
                    ];
                    
                    // Validate required field
                    if (empty($machineData['idMachine'])) {
                        $errorCount++;
                        continue;
                    }
                    
                    // Auto-relate with machine_types, group, and system
                    $machineTypeId = null;
                    if (!empty($machineData['type_name'])) {
                        // Find or create machine type based on type_name
                        $machineType = MachineType::firstOrCreate(
                            ['name' => $machineData['type_name']],
                            [
                                'brand' => $machineData['brand_name'] ?? null,
                                'model' => $machineData['model_name'] ?? null,
                            ]
                        );
                        $machineTypeId = $machineType->id;
                        
                        // Try to find group from machine_erp data or from related room_erp
                        // If group_id is provided in Excel, use it
                        $groupName = trim($data['group'] ?? $data['Group'] ?? $data['group_name'] ?? '');
                        if (!empty($groupName)) {
                            $group = Group::where('name', $groupName)->first();
                            if ($group) {
                                // Update machine type with group
                                $machineType->update(['group_id' => $group->id]);
                                
                                // Sync systems from group
                                $group->load('systems');
                                if ($group->systems->isNotEmpty()) {
                                    $machineType->systems()->sync($group->systems->pluck('id')->toArray());
                                }
                            }
                        }
                        
                        // Auto-create model if model_name is provided
                        if (!empty($machineData['model_name']) && $machineTypeId) {
                            // Try to find brand first if brand_name is provided
                            $brandId = null;
                            if (!empty($machineData['brand_name'])) {
                                $brand = \App\Models\Brand::firstOrCreate(['name' => $machineData['brand_name']]);
                                $brandId = $brand->id;
                            }
                            
                            // Create or find model
                            ModelModel::firstOrCreate(
                                [
                                    'name' => $machineData['model_name'],
                                    'type_id' => $machineTypeId,
                                ],
                                [
                                    'brand_id' => $brandId,
                                ]
                            );
                        }
                    }
                    
                    $machineData['machine_type_id'] = $machineTypeId;
                    
                    // Check for duplicate idMachine before creating
                    $existingMachine = MachineErp::where('idMachine', $machineData['idMachine'])->first();
                    if ($existingMachine) {
                        $duplicateCount++;
                        \Log::warning('Duplicate ID Machine skipped during upload', [
                            'row' => $row,
                            'idMachine' => $machineData['idMachine'],
                            'existing_id' => $existingMachine->id,
                        ]);
                        continue;
                    }
                    
                    MachineErp::create($machineData);
                    $rowCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Error importing machine ERP row: ' . $e->getMessage(), [
                        'row' => $row,
                        'header' => $header,
                        'idMachine' => $machineData['idMachine'] ?? 'N/A',
                    ]);
                }
            }
            
            $message = "Imported $rowCount rows.";
            if ($duplicateCount > 0) {
                $message .= " Skipped $duplicateCount rows with duplicate ID Machine.";
            }
            if ($errorCount > 0) {
                $message .= " Skipped $errorCount rows with errors.";
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error uploading Excel file: ' . $e->getMessage());
            return back()->withErrors(['excel_file' => 'Error reading Excel file: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Excel file with current data
     */
    public function download()
    {
        try {
            $machineErps = MachineErp::orderBy('idMachine', 'asc')->get();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header (sesuai urutan di index)
            $sheet->setCellValue('A1', 'ID Machine');
            $sheet->setCellValue('B1', 'Kode Room');
            $sheet->setCellValue('C1', 'Plant Name');
            $sheet->setCellValue('D1', 'Process Name');
            $sheet->setCellValue('E1', 'Line Name');
            $sheet->setCellValue('F1', 'Room Name');
            $sheet->setCellValue('G1', 'Type Name');
            $sheet->setCellValue('H1', 'Brand Name');
            $sheet->setCellValue('I1', 'Model Name');
            $sheet->setCellValue('J1', 'Status');
            $sheet->setCellValue('K1', 'Serial Number');
            $sheet->setCellValue('L1', 'Tahun Production');
            $sheet->setCellValue('M1', 'No Document');
            $sheet->setCellValue('N1', 'Photo');
            
            // Style header
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ];
            $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);
            
            // Write data (sesuai urutan di index)
            $row = 2;
            foreach ($machineErps as $machineErp) {
                $sheet->setCellValue('A' . $row, $machineErp->idMachine);
                $sheet->setCellValue('B' . $row, $machineErp->kode_room ?? '');
                $sheet->setCellValue('C' . $row, $machineErp->plant_name ?? '');
                $sheet->setCellValue('D' . $row, $machineErp->process_name ?? '');
                $sheet->setCellValue('E' . $row, $machineErp->line_name ?? '');
                $sheet->setCellValue('F' . $row, $machineErp->room_name ?? '');
                $sheet->setCellValue('G' . $row, $machineErp->type_name ?? '');
                $sheet->setCellValue('H' . $row, $machineErp->brand_name ?? '');
                $sheet->setCellValue('I' . $row, $machineErp->model_name ?? '');
                $sheet->setCellValue('J' . $row, $machineErp->status ?? '');
                $sheet->setCellValue('K' . $row, $machineErp->serial_number ?? '');
                $sheet->setCellValue('L' . $row, $machineErp->tahun_production ?? '');
                $sheet->setCellValue('M' . $row, $machineErp->no_document ?? '');
                $sheet->setCellValue('N' . $row, $machineErp->photo ?? '');
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'N') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $filename = 'machine_erp_' . date('Y-m-d_His') . '.xlsx';
            
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'machine_erp_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error downloading Excel file: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error generating Excel file: ' . $e->getMessage()]);
        }
    }

    /**
     * Synchronize Type, Model, and Brand from Models table
     * This will update machine_erp records based on matching data in models table
     */
    public function synchronize()
    {
        try {
            $updated = 0;
            $skipped = 0;
            $errors = 0;

            // Get all machine_erp records that have model_name and type_name
            $machineErps = MachineErp::whereNotNull('model_name')
                ->where('model_name', '!=', '')
                ->whereNotNull('type_name')
                ->where('type_name', '!=', '')
                ->get();

            foreach ($machineErps as $machineErp) {
                try {
                    $modelName = trim($machineErp->model_name);
                    $typeName = trim($machineErp->type_name);

                    if (empty($modelName) || empty($typeName)) {
                        $skipped++;
                        continue;
                    }

                    // Find machine type by type_name (case-insensitive)
                    $machineType = MachineType::whereRaw('LOWER(name) = ?', [strtolower($typeName)])->first();

                    if (!$machineType) {
                        $skipped++;
                        continue;
                    }

                    // Find model by name and type_id (case-insensitive)
                    $model = ModelModel::where('type_id', $machineType->id)
                        ->whereRaw('LOWER(name) = ?', [strtolower($modelName)])
                        ->with(['brand', 'machineType'])
                        ->first();

                    if (!$model) {
                        $skipped++;
                        continue;
                    }

                    // Update machine_erp with data from model
                    $updateData = [];

                    // Update type_name from model's machineType
                    if ($model->machineType && $model->machineType->name) {
                        $updateData['type_name'] = $model->machineType->name;
                        $updateData['machine_type_id'] = $model->machineType->id;
                    }

                    // Update model_name from model
                    if ($model->name) {
                        $updateData['model_name'] = $model->name;
                    }

                    // Update brand_name from model's brand
                    if ($model->brand && $model->brand->name) {
                        $updateData['brand_name'] = $model->brand->name;
                    }

                    if (!empty($updateData)) {
                        $machineErp->update($updateData);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error('Error synchronizing machine_erp ID ' . $machineErp->id . ': ' . $e->getMessage());
                }
            }

            $message = "Synchronization completed. $updated records updated, $skipped skipped, $errors errors.";
            return redirect()->route('machine-erp.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error synchronizing machine_erp: ' . $e->getMessage());
            return redirect()->route('machine-erp.index')->withErrors(['error' => 'Error synchronizing: ' . $e->getMessage()]);
        }
    }
}
