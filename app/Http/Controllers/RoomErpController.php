<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomErp;
use App\Models\Line;
use App\Models\DowntimeErp2;
use App\Models\DowntimeErp;
use App\Models\MachineErp;
use App\Models\Activity;
use App\Models\Plant;
use App\Models\Process;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RoomErpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roomErps = RoomErp::orderBy('name', 'asc')->paginate(15);
        return view('room_erp.index', compact('roomErps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lines = Line::with(['process', 'plant'])->orderBy('name')->get();
        return view('room_erp.create', compact('lines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_room' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'category' => 'nullable|in:Production,Supporting,Warehouse,Other',
            'plant_name' => 'nullable|string|max:255',
            'line_name' => 'nullable|string|max:255',
            'process_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        RoomErp::create($validated);
        return redirect()->route('room-erp.index')->with('success', 'Room ERP created successfully.');
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
        $roomErp = RoomErp::findOrFail($id);
        $lines = Line::with(['process', 'plant'])->orderBy('name')->get();
        return view('room_erp.edit', compact('roomErp', 'lines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode_room' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'category' => 'nullable|in:Production,Supporting,Warehouse,Other',
            'plant_name' => 'nullable|string|max:255',
            'line_name' => 'nullable|string|max:255',
            'process_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $roomErp = RoomErp::findOrFail($id);
        $oldKodeRoom = $roomErp->kode_room;
        $newKodeRoom = $validated['kode_room'] ?? null;
        
        DB::beginTransaction();
        try {
            // Update RoomERP
            $roomErp->update($validated);
            
            $downtimeUpdated = 0;
            $machineUpdated = 0;
            $activityUpdated = 0;
            
            // Update based on kode_room (if exists)
            $kodeRoomToMatch = $oldKodeRoom ?: $newKodeRoom;
            if ($kodeRoomToMatch) {
                // Build update data for downtime_erp2
                $downtimeUpdateData = [];
                if ($oldKodeRoom !== $newKodeRoom && $newKodeRoom) {
                    $downtimeUpdateData['kode_room'] = $newKodeRoom;
                }
                if (isset($validated['plant_name'])) {
                    $downtimeUpdateData['plant'] = $validated['plant_name'];
                }
                if (isset($validated['process_name'])) {
                    $downtimeUpdateData['process'] = $validated['process_name'];
                }
                if (isset($validated['line_name'])) {
                    $downtimeUpdateData['line'] = $validated['line_name'];
                }
                if (isset($validated['name'])) {
                    $downtimeUpdateData['roomName'] = $validated['name'];
                }
                
                if (!empty($downtimeUpdateData)) {
                    $downtimeUpdated += DowntimeErp2::where('kode_room', $kodeRoomToMatch)
                        ->update($downtimeUpdateData);
                }
                
                // Build update data for machine_erp
                $machineUpdateData = [];
                if ($oldKodeRoom !== $newKodeRoom && $newKodeRoom) {
                    $machineUpdateData['kode_room'] = $newKodeRoom;
                }
                if (isset($validated['plant_name'])) {
                    $machineUpdateData['plant_name'] = $validated['plant_name'];
                }
                if (isset($validated['process_name'])) {
                    $machineUpdateData['process_name'] = $validated['process_name'];
                }
                if (isset($validated['line_name'])) {
                    $machineUpdateData['line_name'] = $validated['line_name'];
                }
                if (isset($validated['name'])) {
                    $machineUpdateData['room_name'] = $validated['name'];
                }
                
                if (!empty($machineUpdateData)) {
                    $machineUpdated += MachineErp::where('kode_room', $kodeRoomToMatch)
                        ->update($machineUpdateData);
                }
                
                // Build update data for activities
                $activityUpdateData = [];
                if ($oldKodeRoom !== $newKodeRoom && $newKodeRoom) {
                    $activityUpdateData['kode_room'] = $newKodeRoom;
                }
                
                if (!empty($activityUpdateData)) {
                    $activityUpdated += Activity::where('kode_room', $kodeRoomToMatch)
                        ->update($activityUpdateData);
                }
            }
            
            // Also update by matching room_name, plant_name, process_name, line_name
            // This handles cases where kode_room is not set yet or needs to be synced
            // Always run this to ensure data consistency
            if (isset($validated['name']) && isset($validated['plant_name']) && 
                isset($validated['process_name']) && isset($validated['line_name'])) {
                
                // Build query conditions for matching
                $matchConditions = [
                    ['roomName', $validated['name']],
                    ['plant', $validated['plant_name']],
                    ['process', $validated['process_name']],
                    ['line', $validated['line_name']],
                ];
                
                // Update downtime_erp2 by matching room name and other fields
                $downtimeUpdateDataByMatch = [];
                if ($newKodeRoom) {
                    $downtimeUpdateDataByMatch['kode_room'] = $newKodeRoom;
                }
                if (isset($validated['plant_name'])) {
                    $downtimeUpdateDataByMatch['plant'] = $validated['plant_name'];
                }
                if (isset($validated['process_name'])) {
                    $downtimeUpdateDataByMatch['process'] = $validated['process_name'];
                }
                if (isset($validated['line_name'])) {
                    $downtimeUpdateDataByMatch['line'] = $validated['line_name'];
                }
                if (isset($validated['name'])) {
                    $downtimeUpdateDataByMatch['roomName'] = $validated['name'];
                }
                
                if (!empty($downtimeUpdateDataByMatch)) {
                    $downtimeQuery = DowntimeErp2::where('roomName', $validated['name'])
                        ->where('plant', $validated['plant_name'])
                        ->where('process', $validated['process_name'])
                        ->where('line', $validated['line_name']);
                    
                    // If adding new kode_room (not updating existing), only update records without kode_room
                    // If updating existing kode_room or other fields, update all matching records
                    if ($newKodeRoom && !$oldKodeRoom) {
                        $downtimeQuery->where(function($q) {
                            $q->whereNull('kode_room')->orWhere('kode_room', '');
                        });
                    } elseif ($oldKodeRoom && $oldKodeRoom !== $newKodeRoom) {
                        // If kode_room changed, also update records that match by room name
                        // but don't have the old kode_room (they might have different or no kode_room)
                        $downtimeQuery->where(function($q) use ($oldKodeRoom) {
                            $q->where('kode_room', $oldKodeRoom)
                              ->orWhere(function($q2) {
                                  $q2->whereNull('kode_room')->orWhere('kode_room', '');
                              });
                        });
                    }
                    
                    $downtimeMatched = $downtimeQuery->update($downtimeUpdateDataByMatch);
                    $downtimeUpdated += $downtimeMatched;
                }
                
                // Update machine_erp by matching room name and other fields
                $machineUpdateDataByMatch = [];
                if ($newKodeRoom) {
                    $machineUpdateDataByMatch['kode_room'] = $newKodeRoom;
                }
                if (isset($validated['plant_name'])) {
                    $machineUpdateDataByMatch['plant_name'] = $validated['plant_name'];
                }
                if (isset($validated['process_name'])) {
                    $machineUpdateDataByMatch['process_name'] = $validated['process_name'];
                }
                if (isset($validated['line_name'])) {
                    $machineUpdateDataByMatch['line_name'] = $validated['line_name'];
                }
                if (isset($validated['name'])) {
                    $machineUpdateDataByMatch['room_name'] = $validated['name'];
                }
                
                if (!empty($machineUpdateDataByMatch)) {
                    $machineQuery = MachineErp::where('room_name', $validated['name'])
                        ->where('plant_name', $validated['plant_name'])
                        ->where('process_name', $validated['process_name'])
                        ->where('line_name', $validated['line_name']);
                    
                    // If adding new kode_room (not updating existing), only update records without kode_room
                    // Otherwise, update all matching records (for syncing other fields or updating kode_room)
                    if ($newKodeRoom && !$oldKodeRoom) {
                        // Only update records without kode_room when adding new kode_room
                        $machineQuery->where(function($q) {
                            $q->whereNull('kode_room')->orWhere('kode_room', '');
                        });
                    }
                    // If updating existing kode_room or other fields, update all matching records
                    // (no additional where clause needed)
                    
                    $machineMatched = $machineQuery->update($machineUpdateDataByMatch);
                    $machineUpdated += $machineMatched;
                }
                
                // Update activities by matching room name and other fields
                $activityUpdateDataByMatch = [];
                if ($newKodeRoom) {
                    $activityUpdateDataByMatch['kode_room'] = $newKodeRoom;
                }
                
                if (!empty($activityUpdateDataByMatch)) {
                    $activityQuery = Activity::where('room_name', $validated['name'])
                        ->where('plant', $validated['plant_name'])
                        ->where('process', $validated['process_name'])
                        ->where('line', $validated['line_name']);
                    
                    // If adding new kode_room (not updating existing), only update records without kode_room
                    // Otherwise, update all matching records (for syncing other fields or updating kode_room)
                    if ($newKodeRoom && !$oldKodeRoom) {
                        // Only update records without kode_room when adding new kode_room
                        $activityQuery->where(function($q) {
                            $q->whereNull('kode_room')->orWhere('kode_room', '');
                        });
                    }
                    // If updating existing kode_room or other fields, update all matching records
                    // (no additional where clause needed)
                    
                    $activityMatched = $activityQuery->update($activityUpdateDataByMatch);
                    $activityUpdated += $activityMatched;
                }
            }
            
            DB::commit();
            
            $message = 'Room ERP updated successfully.';
            $updates = [];
            if ($downtimeUpdated > 0) $updates[] = "{$downtimeUpdated} downtime record(s)";
            if ($machineUpdated > 0) $updates[] = "{$machineUpdated} machine record(s)";
            if ($activityUpdated > 0) $updates[] = "{$activityUpdated} activity record(s)";
            
            if (!empty($updates)) {
                $message .= ' Related data updated: ' . implode(', ', $updates) . '.';
            }
            
            return redirect()->route('room-erp.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating RoomERP and related data: ' . $e->getMessage());
            return redirect()->route('room-erp.index')
                ->withErrors(['error' => 'Error updating Room ERP: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $roomErp = RoomErp::findOrFail($id);
        $roomErp->delete();
        return redirect()->route('room-erp.index')->with('success', 'Room ERP deleted successfully.');
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
                    
                    // Map Excel columns to database columns
                    $roomData = [
                        'kode_room' => trim($data['kode_room'] ?? $data['Kode Room'] ?? $data['kodeRoom'] ?? '') ?: null,
                        'name' => trim($data['name'] ?? $data['Name'] ?? ''),
                        'category' => trim($data['category'] ?? $data['Category'] ?? '') ?: null,
                        'plant_name' => trim($data['plant_name'] ?? $data['Plant Name'] ?? $data['plantName'] ?? '') ?: null,
                        'line_name' => trim($data['line_name'] ?? $data['Line Name'] ?? $data['lineName'] ?? '') ?: null,
                        'process_name' => trim($data['process_name'] ?? $data['Process Name'] ?? $data['processName'] ?? '') ?: null,
                        'description' => trim($data['description'] ?? $data['Description'] ?? '') ?: null,
                    ];
                    
                    // Validate required field
                    if (empty($roomData['name'])) {
                        $errorCount++;
                        continue;
                    }
                    
                    RoomErp::create($roomData);
                    $rowCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Error importing room ERP row: ' . $e->getMessage(), [
                        'row' => $row,
                        'header' => $header,
                    ]);
                }
            }
            
            $message = "Imported $rowCount rows.";
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
            $roomErps = RoomErp::orderBy('name', 'asc')->get();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'Kode Room');
            $sheet->setCellValue('B1', 'Name');
            $sheet->setCellValue('C1', 'Category');
            $sheet->setCellValue('D1', 'Plant Name');
            $sheet->setCellValue('E1', 'Line Name');
            $sheet->setCellValue('F1', 'Process Name');
            $sheet->setCellValue('G1', 'Description');
            
            // Style header
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            
            // Write data
            $row = 2;
            foreach ($roomErps as $roomErp) {
                $sheet->setCellValue('A' . $row, $roomErp->kode_room ?? '');
                $sheet->setCellValue('B' . $row, $roomErp->name);
                $sheet->setCellValue('C' . $row, $roomErp->category ?? '');
                $sheet->setCellValue('D' . $row, $roomErp->plant_name ?? '');
                $sheet->setCellValue('E' . $row, $roomErp->line_name ?? '');
                $sheet->setCellValue('F' . $row, $roomErp->process_name ?? '');
                $sheet->setCellValue('G' . $row, $roomErp->description ?? '');
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $filename = 'room_erp_' . date('Y-m-d_His') . '.xlsx';
            
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'room_erp_');
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
     * Preview data from downtime tables before extraction
     */
    public function previewFromDowntime(Request $request)
    {
        // Check if user is admin
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin can access this feature.'
            ], 403);
        }

        $dataSource = $request->input('data_source');

        try {
            $uniqueRooms = [];
            $existingRoomNames = RoomErp::pluck('name')->map(function($name) {
                return strtolower(trim($name));
            })->toArray();

            if ($dataSource === 'downtime_erp') {
                $rooms = DowntimeErp::select('roomName', 'plant', 'process', 'line')
                    ->whereNotNull('roomName')
                    ->where('roomName', '!=', '')
                    ->distinct()
                    ->get();

                foreach ($rooms as $room) {
                    $roomName = trim($room->roomName);
                    $plant = trim($room->plant ?? '');
                    $process = trim($room->process ?? '');
                    $line = trim($room->line ?? '');

                    if (empty($roomName)) continue;

                    $key = strtolower($roomName . '|' . $plant . '|' . $process . '|' . $line);
                    if (!isset($uniqueRooms[$key])) {
                        $uniqueRooms[$key] = [
                            'kode_room' => null,
                            'name' => $roomName,
                            'plant_name' => $plant,
                            'process_name' => $process,
                            'line_name' => $line,
                        ];
                    }
                }
            } else if ($dataSource === 'downtime_erp2') {
                $rooms = DowntimeErp2::select('kode_room', 'roomName', 'plant', 'process', 'line')
                    ->whereNotNull('roomName')
                    ->where('roomName', '!=', '')
                    ->distinct()
                    ->get();

                foreach ($rooms as $room) {
                    $kodeRoom = trim($room->kode_room ?? '');
                    $roomName = trim($room->roomName);
                    $plant = trim($room->plant ?? '');
                    $process = trim($room->process ?? '');
                    $line = trim($room->line ?? '');

                    if (empty($roomName)) continue;

                    // Use kode_room if available, otherwise use combination
                    $key = !empty($kodeRoom) ? strtolower($kodeRoom) : strtolower($roomName . '|' . $plant . '|' . $process . '|' . $line);
                    if (!isset($uniqueRooms[$key])) {
                        $uniqueRooms[$key] = [
                            'kode_room' => !empty($kodeRoom) ? $kodeRoom : null,
                            'name' => $roomName,
                            'plant_name' => $plant,
                            'process_name' => $process,
                            'line_name' => $line,
                        ];
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data source'
                ], 400);
            }

            $totalUnique = count($uniqueRooms);
            $newCount = 0;
            $existingCount = 0;
            $sampleData = [];

            foreach ($uniqueRooms as $room) {
                $roomNameLower = strtolower(trim($room['name']));
                if (in_array($roomNameLower, $existingRoomNames)) {
                    $existingCount++;
                } else {
                    $newCount++;
                    if (count($sampleData) < 20) {
                        $sampleData[] = $room;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'total_unique' => $totalUnique,
                'new_count' => $newCount,
                'existing_count' => $existingCount,
                'sample_data' => array_values($sampleData)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error previewing Room ERP from downtime: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error previewing data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract unique Room ERP data from downtime tables
     */
    public function extractFromDowntime(Request $request)
    {
        // Check if user is admin
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('room-erp.index')
                ->with('error', 'Unauthorized. Only admin can access this feature.');
        }

        $dataSource = $request->input('data_source');

        DB::beginTransaction();
        try {
            $uniqueRooms = [];
            $created = 0;
            $skipped = 0;
            $errors = [];

            if ($dataSource === 'downtime_erp') {
                $rooms = DowntimeErp::select('roomName', 'plant', 'process', 'line')
                    ->whereNotNull('roomName')
                    ->where('roomName', '!=', '')
                    ->distinct()
                    ->get();

                foreach ($rooms as $room) {
                    $roomName = trim($room->roomName);
                    $plant = trim($room->plant ?? '');
                    $process = trim($room->process ?? '');
                    $line = trim($room->line ?? '');

                    if (empty($roomName)) continue;

                    $key = strtolower($roomName . '|' . $plant . '|' . $process . '|' . $line);
                    if (!isset($uniqueRooms[$key])) {
                        $uniqueRooms[$key] = [
                            'kode_room' => null,
                            'name' => $roomName,
                            'plant_name' => $plant,
                            'process_name' => $process,
                            'line_name' => $line,
                        ];
                    }
                }
            } else if ($dataSource === 'downtime_erp2') {
                $rooms = DowntimeErp2::select('kode_room', 'roomName', 'plant', 'process', 'line')
                    ->whereNotNull('roomName')
                    ->where('roomName', '!=', '')
                    ->distinct()
                    ->get();

                foreach ($rooms as $room) {
                    $kodeRoom = trim($room->kode_room ?? '');
                    $roomName = trim($room->roomName);
                    $plant = trim($room->plant ?? '');
                    $process = trim($room->process ?? '');
                    $line = trim($room->line ?? '');

                    if (empty($roomName)) continue;

                    $key = !empty($kodeRoom) ? strtolower($kodeRoom) : strtolower($roomName . '|' . $plant . '|' . $process . '|' . $line);
                    if (!isset($uniqueRooms[$key])) {
                        $uniqueRooms[$key] = [
                            'kode_room' => !empty($kodeRoom) ? $kodeRoom : null,
                            'name' => $roomName,
                            'plant_name' => $plant,
                            'process_name' => $process,
                            'line_name' => $line,
                        ];
                    }
                }
            } else {
                return redirect()->route('room-erp.index')
                    ->with('error', 'Invalid data source.');
            }

            foreach ($uniqueRooms as $roomData) {
                try {
                    // Check if room already exists by name (case-insensitive)
                    $existingRoom = RoomErp::whereRaw('LOWER(name) = ?', [strtolower(trim($roomData['name']))])
                        ->first();

                    if ($existingRoom) {
                        $skipped++;
                        continue;
                    }

                    // Find or create Plant
                    $plant = null;
                    if (!empty($roomData['plant_name'])) {
                        $plant = Plant::firstOrCreate(
                            ['name' => $roomData['plant_name']],
                            ['name' => $roomData['plant_name']]
                        );
                    }

                    // Find or create Process
                    $process = null;
                    if (!empty($roomData['process_name']) && $plant) {
                        $process = Process::firstOrCreate(
                            [
                                'name' => $roomData['process_name'],
                                'plant_id' => $plant->id
                            ],
                            [
                                'name' => $roomData['process_name'],
                                'plant_id' => $plant->id
                            ]
                        );
                    }

                    // Find or create Line
                    $line = null;
                    if (!empty($roomData['line_name']) && $process && $plant) {
                        $line = Line::firstOrCreate(
                            [
                                'name' => $roomData['line_name'],
                                'process_id' => $process->id,
                                'plant_id' => $plant->id
                            ],
                            [
                                'name' => $roomData['line_name'],
                                'process_id' => $process->id,
                                'plant_id' => $plant->id
                            ]
                        );
                    }

                    // Create Room ERP
                    RoomErp::create([
                        'kode_room' => $roomData['kode_room'],
                        'name' => $roomData['name'],
                        'plant_name' => $roomData['plant_name'],
                        'process_name' => $roomData['process_name'],
                        'line_name' => $roomData['line_name'],
                        'category' => null,
                        'description' => null,
                    ]);

                    $created++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Error creating room '{$roomData['name']}': " . $e->getMessage();
                    \Log::error('Error creating Room ERP: ' . $e->getMessage(), ['room_data' => $roomData]);
                }
            }

            DB::commit();

            $message = "Extraction completed. Created: {$created}, Skipped: {$skipped}.";
            return redirect()->route('room-erp.index')
                ->with('success', $message)
                ->with('extraction_details', [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error extracting Room ERP from downtime: ' . $e->getMessage());
            return redirect()->route('room-erp.index')
                ->with('error', 'Error extracting data: ' . $e->getMessage());
        }
    }
}
