<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\User;
use App\Models\MachineErp;
use App\Models\RoomErp;
use App\Helpers\ImageHelper;
use App\Helpers\DataFilterHelper;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Activity::query();
        
        // Filter by user role (mekanik only sees their own data)
        if (DataFilterHelper::shouldFilterRoute(request()->route()->getName())) {
            DataFilterHelper::filterByUserRole($query, auth()->user(), 'id_mekanik');
        }
        
        $activities = $query->orderBy('date', 'desc')->orderBy('start', 'desc')->paginate(10);
        
        // Get Room ERPs for bulk edit dropdown (only for admin)
        $roomErps = [];
        if (auth()->user()->role === 'admin') {
            $roomErps = RoomErp::orderBy('name', 'asc')->get();
        }
        
        return view('activities.index', compact('activities', 'roomErps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all mekanik (team member) users for auto-complete
        $mekaniksQuery = User::where('role', 'mekanik')
            ->orderBy('name', 'asc')
            ->get();
        
        // Map mekaniks data for JavaScript
        $mekaniks = [];
        foreach ($mekaniksQuery as $m) {
            $mekaniks[] = [
                'id' => (string)$m->id,
                'nik' => $m->nik ?? '',
                'name' => $m->name ?? '',
            ];
        }
        
        // Get all machine ERP for auto-complete
        $machinesQuery = MachineErp::orderBy('idMachine', 'asc')->get();
        
        // Map machines data for JavaScript
        $machines = [];
        foreach ($machinesQuery as $machine) {
            $machines[] = [
                'id' => (string)$machine->id,
                'idMachine' => $machine->idMachine ?? '',
                'typeMachine' => $machine->type_name ?? '',
                'modelMachine' => $machine->model_name ?? '',
                'brandMachine' => $machine->brand_name ?? '',
                'plant' => $machine->plant_name ?? '',
                'process' => $machine->process_name ?? '',
                'line' => $machine->line_name ?? '',
                'roomName' => $machine->room_name ?? '',
            ];
        }
        
        // Get RoomErp for dropdown (same format as MachineErp)
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        
        // Get current logged in user
        $currentUser = auth()->user();
        
        return view('activities.create', compact('mekaniks', 'machines', 'roomErps', 'currentUser'));
    }

    /**
     * Search mechanic for auto-complete
     */
    public function searchMechanic(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $mechanics = User::where('role', 'mekanik')
            ->where(function($q) use ($query) {
                $q->where('nik', 'like', '%' . $query . '%')
                  ->orWhere('name', 'like', '%' . $query . '%');
            })
            ->orderBy('nik')
            ->limit(10)
            ->get(['id', 'nik', 'name']);
        
        return response()->json($mechanics);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'kode_room' => 'nullable|string|max:255',
            'plant' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'line' => 'nullable|string|max:255',
            'room_name' => 'nullable|string|max:255',
            'start' => 'required|date_format:H:i',
            'stop' => 'required|date_format:H:i',
            'duration' => 'nullable|integer',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'id_mekanik' => 'required|string|max:255',
            'nama_mekanik' => 'required|string|max:255',
            'id_mesin' => 'nullable|string|max:255',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB per photo
        ]);

        // Validate max 3 photos
        if ($request->hasFile('photos')) {
            $photosCount = count($request->file('photos'));
            if ($photosCount > 3) {
                return back()->withInput()->withErrors(['photos' => 'Maximum 3 photos allowed.']);
            }
        }

        // Calculate duration if not provided
        if (empty($validated['duration']) && !empty($validated['start']) && !empty($validated['stop'])) {
            $validated['duration'] = $this->calculateDuration($validated['start'], $validated['stop']);
        }

        // Handle photo uploads (max 3)
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPath = ImageHelper::convertToWebP($photo, 'activities', 85);
                $photos[] = $photoPath;
            }
        }
        $validated['photos'] = !empty($photos) ? $photos : null;

        Activity::create($validated);
        return redirect()->route('activities.index')->with('success', 'Activity created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $activity = Activity::findOrFail($id);
        return view('activities.show', compact('activity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);
        $page = $request->query('page', 1);
        
        // Get all mekanik (team member) users for auto-complete
        $mekaniksQuery = User::where('role', 'mekanik')
            ->orderBy('name', 'asc')
            ->get();
        
        // Map mekaniks data for JavaScript
        $mekaniks = [];
        foreach ($mekaniksQuery as $m) {
            $mekaniks[] = [
                'id' => (string)$m->id,
                'nik' => $m->nik ?? '',
                'name' => $m->name ?? '',
            ];
        }
        
        // Get all machine ERP for auto-complete
        $machinesQuery = MachineErp::orderBy('idMachine', 'asc')->get();
        
        // Map machines data for JavaScript
        $machines = [];
        foreach ($machinesQuery as $machine) {
            $machines[] = [
                'id' => (string)$machine->id,
                'idMachine' => $machine->idMachine ?? '',
                'typeMachine' => $machine->type_name ?? '',
                'modelMachine' => $machine->model_name ?? '',
                'brandMachine' => $machine->brand_name ?? '',
                'plant' => $machine->plant_name ?? '',
                'process' => $machine->process_name ?? '',
                'line' => $machine->line_name ?? '',
                'roomName' => $machine->room_name ?? '',
            ];
        }
        
        // Get RoomErp for dropdown (same format as MachineErp)
        $roomErps = RoomErp::orderBy('name', 'asc')->get();
        
        return view('activities.edit', compact('activity', 'mekaniks', 'machines', 'roomErps', 'page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'kode_room' => 'nullable|string|max:255',
            'plant' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'line' => 'nullable|string|max:255',
            'room_name' => 'nullable|string|max:255',
            'start' => 'required|date_format:H:i',
            'stop' => 'required|date_format:H:i',
            'duration' => 'nullable|integer',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'id_mekanik' => 'nullable|string|max:255',
            'nama_mekanik' => 'nullable|string|max:255',
            'id_mesin' => 'nullable|string|max:255',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB per photo
            'existing_photos' => 'nullable|array',
            'existing_photos.*' => 'nullable|string',
        ]);

        // Validate max 3 photos
        if ($request->hasFile('photos')) {
            $photosCount = count($request->file('photos'));
            if ($photosCount > 3) {
                return back()->withInput()->withErrors(['photos' => 'Maximum 3 photos allowed.']);
            }
        }

        // Calculate duration if not provided
        if (empty($validated['duration']) && !empty($validated['start']) && !empty($validated['stop'])) {
            $validated['duration'] = $this->calculateDuration($validated['start'], $validated['stop']);
        }

        $activity = Activity::findOrFail($id);
        
        // Handle photo uploads (max 3)
        // Get existing photos that are kept (from form)
        $keptExistingPhotos = $request->input('existing_photos', []);
        
        // Get new photos from upload
        $newPhotos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPath = ImageHelper::convertToWebP($photo, 'activities', 85);
                $newPhotos[] = $photoPath;
            }
        }
            
        // Merge kept existing photos with new photos (limit to 3 total)
        $allPhotos = array_merge($keptExistingPhotos, $newPhotos);
        $validated['photos'] = !empty($allPhotos) ? array_slice($allPhotos, 0, 3) : [];
        
        // Delete removed photos from storage
        $existingPhotos = $activity->photos ?? [];
        foreach ($existingPhotos as $oldPhoto) {
            if (!in_array($oldPhoto, $keptExistingPhotos)) {
                // Photo was removed, delete from storage
                if (Storage::disk('public')->exists($oldPhoto)) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }
        }

        $activity->update($validated);
        
        // Get page from request or default to 1
        $page = $request->input('page', 1);
        
        return redirect()->route('activities.index', ['page' => $page])
            ->with('success', 'Activity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();
        return redirect()->route('activities.index')->with('success', 'Activity deleted successfully.');
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
                        $plant = !empty(trim($data['plant'] ?? $data['Plant'] ?? '')) ? trim($data['plant'] ?? $data['Plant'] ?? '') : null;
                    }
                    if (!$process) {
                        $process = !empty(trim($data['process'] ?? $data['Process'] ?? '')) ? trim($data['process'] ?? $data['Process'] ?? '') : null;
                    }
                    if (!$line) {
                        $line = !empty(trim($data['line'] ?? $data['Line'] ?? '')) ? trim($data['line'] ?? $data['Line'] ?? '') : null;
                    }
                    if (!$roomName) {
                        $roomName = !empty(trim($data['room_name'] ?? $data['Room Name'] ?? $data['roomName'] ?? '')) ? trim($data['room_name'] ?? $data['Room Name'] ?? $data['roomName'] ?? '') : null;
                    }
                    
                    // Map Excel columns to database columns
                    $activityData = [
                        'date' => $this->parseDate($data['date'] ?? $data['Date'] ?? ''),
                        'kode_room' => $kodeRoom,
                        'plant' => $plant,
                        'process' => $process,
                        'line' => $line,
                        'room_name' => $roomName,
                        'start' => $this->parseTime($data['start'] ?? $data['Start'] ?? ''),
                        'stop' => $this->parseTime($data['stop'] ?? $data['Stop'] ?? ''),
                        'description' => !empty(trim($data['description'] ?? $data['Description'] ?? '')) ? trim($data['description'] ?? $data['Description'] ?? '') : null,
                        'remarks' => !empty(trim($data['remarks'] ?? $data['Remarks'] ?? '')) ? trim($data['remarks'] ?? $data['Remarks'] ?? '') : null,
                        'id_mekanik' => !empty(trim($data['id_mekanik'] ?? $data['ID Mekanik'] ?? $data['idMekanik'] ?? '')) ? trim($data['id_mekanik'] ?? $data['ID Mekanik'] ?? $data['idMekanik'] ?? '') : null,
                        'nama_mekanik' => null, // Will be auto-filled from id_mekanik
                        'id_mesin' => !empty(trim($data['id_mesin'] ?? $data['ID Mesin'] ?? $data['idMesin'] ?? '')) ? trim($data['id_mesin'] ?? $data['ID Mesin'] ?? $data['idMesin'] ?? '') : null,
                    ];
                    
                    // Auto-fill nama_mekanik from id_mekanik by looking up in Users table
                    if (!empty($activityData['id_mekanik'])) {
                        // Try to find user by nik (NIK) or id
                        $user = User::where('nik', $activityData['id_mekanik'])
                            ->orWhere('id', $activityData['id_mekanik'])
                            ->first();
                        
                        if ($user) {
                            $activityData['nama_mekanik'] = $user->name;
                        } else {
                            // If not found in Users, use the value from Excel if provided
                            $activityData['nama_mekanik'] = !empty(trim($data['nama_mekanik'] ?? $data['Nama Mekanik'] ?? $data['namaMekanik'] ?? '')) 
                                ? trim($data['nama_mekanik'] ?? $data['Nama Mekanik'] ?? $data['namaMekanik'] ?? '') 
                                : null;
                        }
                    } else {
                        // If id_mekanik is empty, try to use nama_mekanik from Excel
                        $activityData['nama_mekanik'] = !empty(trim($data['nama_mekanik'] ?? $data['Nama Mekanik'] ?? $data['namaMekanik'] ?? '')) 
                            ? trim($data['nama_mekanik'] ?? $data['Nama Mekanik'] ?? $data['namaMekanik'] ?? '') 
                            : null;
                    }
                    
                    // Calculate duration
                    if (!empty($activityData['start']) && !empty($activityData['stop'])) {
                        $activityData['duration'] = $this->calculateDuration($activityData['start'], $activityData['stop']);
                    } else {
                        $activityData['duration'] = !empty(trim($data['duration'] ?? $data['Duration'] ?? '')) ? (int)trim($data['duration'] ?? $data['Duration'] ?? '') : null;
                    }
                    
                    // Validate required fields (only date, start, and stop are required now)
                    if (empty($activityData['date']) || empty($activityData['start']) || empty($activityData['stop'])) {
                        $errorCount++;
                        continue;
                    }
                    
                    Activity::create($activityData);
                    $rowCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Error importing activity row: ' . $e->getMessage(), [
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
            $activities = Activity::orderBy('date', 'desc')->orderBy('start', 'desc')->get();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'Date');
            $sheet->setCellValue('B1', 'Plant');
            $sheet->setCellValue('C1', 'Process');
            $sheet->setCellValue('D1', 'Line');
            $sheet->setCellValue('E1', 'Room Name');
            $sheet->setCellValue('F1', 'Start');
            $sheet->setCellValue('G1', 'Stop');
            $sheet->setCellValue('H1', 'Duration');
            $sheet->setCellValue('I1', 'Description');
            $sheet->setCellValue('J1', 'Remarks');
            $sheet->setCellValue('K1', 'ID Mekanik');
            $sheet->setCellValue('L1', 'Nama Mekanik');
            
            // Style header
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ];
            $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
            
            // Write data
            $row = 2;
            foreach ($activities as $activity) {
                $sheet->setCellValue('A' . $row, $activity->date);
                $sheet->setCellValue('B' . $row, $activity->plant);
                $sheet->setCellValue('C' . $row, $activity->process);
                $sheet->setCellValue('D' . $row, $activity->line);
                $sheet->setCellValue('E' . $row, $activity->room_name);
                $sheet->setCellValue('F' . $row, $activity->start);
                $sheet->setCellValue('G' . $row, $activity->stop);
                $sheet->setCellValue('H' . $row, $activity->duration ?? '');
                $sheet->setCellValue('I' . $row, $activity->description ?? '');
                $sheet->setCellValue('J' . $row, $activity->remarks ?? '');
                $sheet->setCellValue('K' . $row, $activity->id_mekanik);
                $sheet->setCellValue('L' . $row, $activity->nama_mekanik);
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $filename = 'activities_' . date('Y-m-d_His') . '.xlsx';
            
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'activities_');
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
     * Calculate duration in minutes from start and stop time
     */
    private function calculateDuration($start, $stop)
    {
        if (empty($start) || empty($stop)) {
            return null;
        }

        try {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $start);
            $stopTime = \Carbon\Carbon::createFromFormat('H:i', $stop);
            
            // Handle case where stop time is next day
            if ($stopTime->lt($startTime)) {
                $stopTime->addDay();
            }
            
            return $startTime->diffInMinutes($stopTime);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }
        
        // If it's already a date object
        if ($dateValue instanceof \DateTime) {
            return $dateValue->format('Y-m-d');
        }
        
        // Try to parse as date string
        try {
            $date = \Carbon\Carbon::parse($dateValue);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // Try Excel date format (numeric)
            if (is_numeric($dateValue)) {
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                    return $date->format('Y-m-d');
                } catch (\Exception $e2) {
                    return null;
                }
            }
            return null;
        }
    }

    /**
     * Parse time from various formats
     */
    private function parseTime($timeValue)
    {
        if (empty($timeValue)) {
            return null;
        }
        
        // If it's already a time object
        if ($timeValue instanceof \DateTime) {
            return $timeValue->format('H:i');
        }
        
        // Try to parse as time string (HH:MM format)
        try {
            // Handle Excel time format (decimal)
            if (is_numeric($timeValue)) {
                $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($timeValue);
                return $time->format('H:i');
            }
            
            // Try to parse as time string
            $time = \Carbon\Carbon::createFromFormat('H:i', $timeValue);
            return $time->format('H:i');
        } catch (\Exception $e) {
            // Try other formats
            try {
                $time = \Carbon\Carbon::createFromFormat('H:i:s', $timeValue);
                return $time->format('H:i');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
    
    /**
     * Batch update location for multiple activities
     */
    public function batchUpdateLocation(Request $request)
    {
        // Only admin can batch update
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'activity_ids' => 'required|array|min:1',
            'activity_ids.*' => 'exists:activities,id',
            'kode_room' => 'nullable|string|max:255',
            'plant' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'line' => 'nullable|string|max:255',
            'room_name' => 'nullable|string|max:255',
        ]);
        
        $activityIds = $validated['activity_ids'];
        $activities = Activity::whereIn('id', $activityIds)->get();
        
        $updatedCount = 0;
        foreach ($activities as $activity) {
            // Only update fields that are provided (not empty)
            if ($request->filled('kode_room')) {
                $activity->kode_room = $validated['kode_room'];
            }
            if ($request->filled('plant')) {
                $activity->plant = $validated['plant'];
            }
            if ($request->filled('process')) {
                $activity->process = $validated['process'];
            }
            if ($request->filled('line')) {
                $activity->line = $validated['line'];
            }
            if ($request->filled('room_name')) {
                $activity->room_name = $validated['room_name'];
            }
            
            $activity->save();
            $updatedCount++;
        }
        
        // Get page from request or default to 1
        $page = $request->input('page', 1);
        
        return redirect()->route('activities.index', ['page' => $page])
            ->with('success', "Successfully updated location for {$updatedCount} activity(ies).");
    }
}
