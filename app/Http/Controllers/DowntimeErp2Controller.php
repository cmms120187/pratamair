<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DowntimeErp2;
use App\Models\MachineErp;
use App\Models\RoomErp;
use App\Models\User;
use App\Models\Group;
use App\Helpers\DataFilterHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DowntimeErp2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = DowntimeErp2::query();
        
        // Filter by user role (mekanik only sees their own data)
        if (DataFilterHelper::shouldFilterRoute(request()->route()->getName())) {
            DataFilterHelper::filterByUserRole($query, auth()->user(), 'idMekanik');
        }
        
        $downtimeErp2s = $query->orderBy('date', 'desc')->paginate(12);
        return view('downtime_erp2.index', compact('downtimeErp2s'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all mekanik (team member) users for auto-complete with atasan relationship
        $mekaniksQuery = User::where('role', 'mekanik')
            ->with(['atasan', 'atasan.atasan', 'atasan.atasan.atasan'])
            ->orderBy('name', 'asc')
            ->get();
        
        // Map mekaniks data for JavaScript
        $mekaniks = [];
        foreach ($mekaniksQuery as $m) {
            // Leader (atasan mekanik - team_leader)
            $leader = $m->atasan;
            
            // GL (atasan dari leader - group_leader)
            $gl = $leader ? $leader->atasan : null;
            
            // Coordinator (atasan dari GL - coordinator)
            $coord = $gl ? $gl->atasan : null;
            
            $mekaniks[] = [
                'id' => (string)$m->id,
                'nik' => $m->nik ?? '',
                'name' => $m->name ?? '',
                'atasan_id' => $m->atasan_id ? (string)$m->atasan_id : null,
                'atasan_nik' => $leader ? ($leader->nik ?? '') : '',
                'atasan_name' => $leader ? ($leader->name ?? '') : '',
                'gl_nik' => $gl ? ($gl->nik ?? '') : '',
                'gl_name' => $gl ? ($gl->name ?? '') : '',
                'coord_nik' => $coord ? ($coord->nik ?? '') : '',
                'coord_name' => $coord ? ($coord->name ?? '') : '',
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
                'kodeRoom' => $machine->kode_room ?? '',
            ];
        }
        
        return view('downtime_erp2.create', compact('mekaniks', 'machines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'kode_room' => 'nullable|string|max:255',
            'plant' => 'required|string|max:255',
            'process' => 'required|string|max:255',
            'line' => 'required|string|max:255',
            'roomName' => 'required|string|max:255',
            'idMachine' => 'required|string|max:255',
            'typeMachine' => 'required|string|max:255',
            'modelMachine' => 'required|string|max:255',
            'brandMachine' => 'required|string|max:255',
            'stopProduction' => 'required|string|max:255',
            'responMechanic' => 'required|string|max:255',
            'startProduction' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'Standar_Time' => 'nullable|string|max:255',
            'problemDowntime' => 'required|string|max:255',
            'Problem_MM' => 'nullable|string|max:255',
            'reasonDowntime' => 'required|string|max:255',
            'actionDowtime' => 'required|string|max:255',
            'Part' => 'nullable|string|max:255',
            'idMekanik' => 'required|string|max:255',
            'nameMekanik' => 'required|string|max:255',
            'idLeader' => 'required|string|max:255',
            'nameLeader' => 'required|string|max:255',
            'idGL' => 'nullable|string|max:255',
            'nameGL' => 'nullable|string|max:255',
            'idCoord' => 'required|string|max:255',
            'nameCoord' => 'required|string|max:255',
            'groupProblem' => 'required|string|max:255',
            'include_oee' => 'nullable|boolean',
        ]);

        // Handle checkbox (include_oee)
        $validated['include_oee'] = $request->has('include_oee') ? true : false;

        // Combine date with time fields (stopProduction, responMechanic, startProduction)
        $date = $validated['date'];
        
        // Format time to HH:MM:SS if needed
        $stopTime = $this->formatTime($validated['stopProduction']);
        $responTime = $this->formatTime($validated['responMechanic']);
        $startTime = $this->formatTime($validated['startProduction']);
        
        // Combine date + time
        $validated['stopProduction'] = $date . ' ' . $stopTime;
        $validated['responMechanic'] = $date . ' ' . $responTime;
        $validated['startProduction'] = $date . ' ' . $startTime;

        DowntimeErp2::create($validated);
        return redirect()->route('downtime-erp2.index')->with('success', 'Downtime ERP2 created successfully.');
    }
    
    /**
     * Format time to HH:MM:SS format
     */
    private function formatTime($time)
    {
        if (empty($time)) {
            return '00:00:00';
        }
        
        // If already in HH:MM:SS format, return as is
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        
        // If in HH:MM format, add :00
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        
        // Try to parse and format
        try {
            $parts = explode(':', $time);
            $hour = str_pad($parts[0] ?? '00', 2, '0', STR_PAD_LEFT);
            $minute = str_pad($parts[1] ?? '00', 2, '0', STR_PAD_LEFT);
            $second = str_pad($parts[2] ?? '00', 2, '0', STR_PAD_LEFT);
            return $hour . ':' . $minute . ':' . $second;
        } catch (\Exception $e) {
            return '00:00:00';
        }
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
    public function edit(Request $request, string $id)
    {
        $downtimeErp2 = DowntimeErp2::findOrFail($id);
        $page = $request->query('page', 1);
        
        // Extract time from datetime fields for form display
        $stopTime = $this->extractTime($downtimeErp2->stopProduction);
        $responTime = $this->extractTime($downtimeErp2->responMechanic);
        $startTime = $this->extractTime($downtimeErp2->startProduction);
        
        // Get all mekanik (team member) users for auto-complete with atasan relationship
        $mekaniksQuery = User::where('role', 'mekanik')
            ->with(['atasan', 'atasan.atasan', 'atasan.atasan.atasan'])
            ->orderBy('name', 'asc')
            ->get();
        
        // Map mekaniks data for JavaScript
        $mekaniks = [];
        foreach ($mekaniksQuery as $m) {
            // Leader (atasan mekanik - team_leader)
            $leader = $m->atasan;
            
            // GL (atasan dari leader - group_leader)
            $gl = $leader ? $leader->atasan : null;
            
            // Coordinator (atasan dari GL - coordinator)
            $coord = $gl ? $gl->atasan : null;
            
            $mekaniks[] = [
                'id' => (string)$m->id,
                'nik' => $m->nik ?? '',
                'name' => $m->name ?? '',
                'atasan_id' => $m->atasan_id ? (string)$m->atasan_id : null,
                'atasan_nik' => $leader ? ($leader->nik ?? '') : '',
                'atasan_name' => $leader ? ($leader->name ?? '') : '',
                'gl_nik' => $gl ? ($gl->nik ?? '') : '',
                'gl_name' => $gl ? ($gl->name ?? '') : '',
                'coord_nik' => $coord ? ($coord->nik ?? '') : '',
                'coord_name' => $coord ? ($coord->name ?? '') : '',
            ];
        }
        
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
        
        return view('downtime_erp2.edit', compact('downtimeErp2', 'stopTime', 'responTime', 'startTime', 'mekaniks', 'groups', 'groupsData', 'page'));
    }
    
    /**
     * Extract time (HH:MM:SS) from datetime string
     */
    private function extractTime($datetime)
    {
        if (empty($datetime)) {
            return '';
        }
        
        try {
            // Try to parse as Carbon date
            $carbon = \Carbon\Carbon::parse($datetime);
            return $carbon->format('H:i:s');
        } catch (\Exception $e) {
            // If parsing fails, try to extract time manually
            if (strpos($datetime, ' ') !== false) {
                $parts = explode(' ', $datetime);
                if (isset($parts[1])) {
                    $time = substr($parts[1], 0, 8);
                    // Ensure format is HH:MM:SS
                    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                        return $time;
                    } elseif (preg_match('/^\d{2}:\d{2}$/', $time)) {
                        return $time . ':00';
                    }
                }
            }
            // If it's already in time format
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $datetime)) {
                return $datetime;
            } elseif (preg_match('/^\d{2}:\d{2}$/', $datetime)) {
                return $datetime . ':00';
            }
            return '';
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'kode_room' => 'nullable|string|max:255',
            'plant' => 'required|string|max:255',
            'process' => 'required|string|max:255',
            'line' => 'required|string|max:255',
            'roomName' => 'required|string|max:255',
            'idMachine' => 'required|string|max:255',
            'typeMachine' => 'required|string|max:255',
            'modelMachine' => 'required|string|max:255',
            'brandMachine' => 'required|string|max:255',
            'stopProduction' => 'required|string|max:255',
            'responMechanic' => 'required|string|max:255',
            'startProduction' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'Standar_Time' => 'nullable|string|max:255',
            'problemDowntime' => 'required|string|max:255',
            'Problem_MM' => 'nullable|string|max:255',
            'reasonDowntime' => 'required|string|max:255',
            'actionDowtime' => 'required|string|max:255',
            'Part' => 'nullable|string|max:255',
            'idMekanik' => 'required|string|max:255',
            'nameMekanik' => 'required|string|max:255',
            'idLeader' => 'required|string|max:255',
            'nameLeader' => 'required|string|max:255',
            'idGL' => 'nullable|string|max:255',
            'nameGL' => 'nullable|string|max:255',
            'idCoord' => 'required|string|max:255',
            'nameCoord' => 'required|string|max:255',
            'groupProblem' => 'required|string|max:255',
            'include_oee' => 'nullable|boolean',
        ]);

        // Handle checkbox (include_oee)
        $validated['include_oee'] = $request->has('include_oee') ? true : false;

        // Combine date with time fields (stopProduction, responMechanic, startProduction)
        $date = $validated['date'];
        
        // Format time to HH:MM:SS if needed
        $stopTime = $this->formatTime($validated['stopProduction']);
        $responTime = $this->formatTime($validated['responMechanic']);
        $startTime = $this->formatTime($validated['startProduction']);
        
        // Combine date + time
        $validated['stopProduction'] = $date . ' ' . $stopTime;
        $validated['responMechanic'] = $date . ' ' . $responTime;
        $validated['startProduction'] = $date . ' ' . $startTime;

        $downtimeErp2 = DowntimeErp2::findOrFail($id);
        $downtimeErp2->update($validated);
        
        // Get page from request or default to 1
        $page = $request->input('page', 1);
        
        return redirect()->route('downtime-erp2.index', ['page' => $page])
            ->with('success', 'Downtime ERP2 updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $downtimeErp2 = DowntimeErp2::findOrFail($id);
        $downtimeErp2->delete();
        return redirect()->route('downtime-erp2.index')->with('success', 'Downtime ERP2 deleted successfully.');
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
                    
                    // If kode_room is provided, lookup RoomERP to get plant, process, line, and roomName
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
                        $plant = trim($data['plant'] ?? $data['Plant'] ?? '') ?: '';
                    }
                    if (!$process) {
                        $process = trim($data['process'] ?? $data['Process'] ?? '') ?: '';
                    }
                    if (!$line) {
                        $line = trim($data['line'] ?? $data['Line'] ?? '') ?: '';
                    }
                    if (!$roomName) {
                        $roomName = trim($data['roomName'] ?? $data['Room Name'] ?? $data['room_name'] ?? '') ?: '';
                    }
                    
                    // Map Excel columns to database columns (handle various column name formats)
                    $downtimeData = [
                        'date' => $this->parseDate($data['date'] ?? $data['Date'] ?? ''),
                        'kode_room' => $kodeRoom,
                        'plant' => $plant,
                        'process' => $process,
                        'line' => $line,
                        'roomName' => $roomName,
                        'idMachine' => trim($data['idMachine'] ?? $data['ID Machine'] ?? $data['id_machine'] ?? '') ?: '',
                        'typeMachine' => trim($data['typeMachine'] ?? $data['Type Machine'] ?? $data['type_machine'] ?? '') ?: '',
                        'modelMachine' => trim($data['modelMachine'] ?? $data['Model Machine'] ?? $data['model_machine'] ?? '') ?: '',
                        'brandMachine' => trim($data['brandMachine'] ?? $data['Brand Machine'] ?? $data['brand_machine'] ?? '') ?: '',
                        'stopProduction' => trim($data['stopProduction'] ?? $data['Stop Production'] ?? $data['stop_production'] ?? '') ?: '',
                        'responMechanic' => trim($data['responMechanic'] ?? $data['Respon Mechanic'] ?? $data['respon_mechanic'] ?? '') ?: '',
                        'startProduction' => trim($data['startProduction'] ?? $data['Start Production'] ?? $data['start_production'] ?? '') ?: '',
                        'duration' => trim($data['duration'] ?? $data['Duration'] ?? '') ?: '',
                        'Standar_Time' => trim($data['Standar_Time'] ?? $data['Standar Time'] ?? $data['standar_time'] ?? '') ?: null,
                        'problemDowntime' => trim($data['problemDowntime'] ?? $data['Problem Downtime'] ?? $data['problem_downtime'] ?? '') ?: '',
                        'Problem_MM' => trim($data['Problem_MM'] ?? $data['Problem MM'] ?? $data['problem_mm'] ?? '') ?: null,
                        'reasonDowntime' => trim($data['reasonDowntime'] ?? $data['Reason Downtime'] ?? $data['reason_downtime'] ?? '') ?: '',
                        'actionDowtime' => trim($data['actionDowtime'] ?? $data['Action Downtime'] ?? $data['action_downtime'] ?? '') ?: '',
                        'Part' => trim($data['Part'] ?? $data['part'] ?? '') ?: null,
                        'idMekanik' => trim($data['idMekanik'] ?? $data['ID Mekanik'] ?? $data['id_mekanik'] ?? '') ?: '',
                        'nameMekanik' => trim($data['nameMekanik'] ?? $data['Name Mekanik'] ?? $data['name_mekanik'] ?? '') ?: '',
                        'idLeader' => trim($data['idLeader'] ?? $data['ID Leader'] ?? $data['id_leader'] ?? '') ?: '',
                        'nameLeader' => trim($data['nameLeader'] ?? $data['Name Leader'] ?? $data['name_leader'] ?? '') ?: '',
                        'idGL' => trim($data['idGL'] ?? $data['ID GL'] ?? $data['id_gl'] ?? '') ?: null,
                        'nameGL' => trim($data['nameGL'] ?? $data['Name GL'] ?? $data['name_gl'] ?? '') ?: null,
                        'idCoord' => trim($data['idCoord'] ?? $data['ID Coord'] ?? $data['id_coord'] ?? '') ?: '',
                        'nameCoord' => trim($data['nameCoord'] ?? $data['Name Coord'] ?? $data['name_coord'] ?? '') ?: '',
                        'groupProblem' => trim($data['groupProblem'] ?? $data['Group Problem'] ?? $data['group_problem'] ?? '') ?: '',
                    ];
                    
                    // Validate required fields
                    if (empty($downtimeData['date']) || empty($downtimeData['idMachine'])) {
                        $errorCount++;
                        continue;
                    }
                    
                    // Check if ID Machine exists in machine_erp table
                    $machineErp = MachineErp::where('idMachine', $downtimeData['idMachine'])->first();
                    
                    if ($machineErp) {
                        // Update location data from machine_erp
                        if (!empty($machineErp->plant_name)) {
                            $downtimeData['plant'] = $machineErp->plant_name;
                        }
                        if (!empty($machineErp->process_name)) {
                            $downtimeData['process'] = $machineErp->process_name;
                        }
                        if (!empty($machineErp->line_name)) {
                            $downtimeData['line'] = $machineErp->line_name;
                        }
                        if (!empty($machineErp->room_name)) {
                            $downtimeData['roomName'] = $machineErp->room_name;
                        }
                        
                        // Update machine detail from machine_erp
                        if (!empty($machineErp->type_name)) {
                            $downtimeData['typeMachine'] = $machineErp->type_name;
                        }
                        if (!empty($machineErp->brand_name)) {
                            $downtimeData['brandMachine'] = $machineErp->brand_name;
                        }
                        if (!empty($machineErp->model_name)) {
                            $downtimeData['modelMachine'] = $machineErp->model_name;
                        }
                    }
                    
                    // Ensure required fields are not empty
                    if (empty($downtimeData['plant'])) {
                        $downtimeData['plant'] = '';
                    }
                    if (empty($downtimeData['process'])) {
                        $downtimeData['process'] = '';
                    }
                    if (empty($downtimeData['line'])) {
                        $downtimeData['line'] = '';
                    }
                    if (empty($downtimeData['roomName'])) {
                        $downtimeData['roomName'] = '';
                    }
                    if (empty($downtimeData['typeMachine'])) {
                        $downtimeData['typeMachine'] = '';
                    }
                    if (empty($downtimeData['brandMachine'])) {
                        $downtimeData['brandMachine'] = '';
                    }
                    if (empty($downtimeData['modelMachine'])) {
                        $downtimeData['modelMachine'] = '';
                    }
                    
                    // Check if ID Mekanik (NIK) exists in users table
                    if (!empty($downtimeData['idMekanik'])) {
                        $mekanik = User::where('nik', $downtimeData['idMekanik'])
                            ->orWhere('id', $downtimeData['idMekanik'])
                            ->with('atasan')
                            ->first();
                        
                        if ($mekanik) {
                            // Update mekanik data
                            $downtimeData['idMekanik'] = $mekanik->nik ?? $downtimeData['idMekanik'];
                            $downtimeData['nameMekanik'] = $mekanik->name ?? $downtimeData['nameMekanik'] ?? '';
                            
                            // Update leader data from atasan
                            if ($mekanik->atasan) {
                                $downtimeData['idLeader'] = $mekanik->atasan->nik ?? $downtimeData['idLeader'] ?? '';
                                $downtimeData['nameLeader'] = $mekanik->atasan->name ?? $downtimeData['nameLeader'] ?? '';
                            }
                        }
                    }
                    
                    // Ensure required fields are not empty
                    if (empty($downtimeData['nameMekanik'])) {
                        $downtimeData['nameMekanik'] = '';
                    }
                    if (empty($downtimeData['idLeader'])) {
                        $downtimeData['idLeader'] = '';
                    }
                    if (empty($downtimeData['nameLeader'])) {
                        $downtimeData['nameLeader'] = '';
                    }
                    
                    DowntimeErp2::create($downtimeData);
                    $rowCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Error importing downtime ERP2 row: ' . $e->getMessage(), [
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
            $downtimeErp2s = DowntimeErp2::orderBy('date', 'desc')->get();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $headers = [
                'Date', 'Plant', 'Process', 'Line', 'Room Name', 'ID Machine', 'Type Machine', 
                'Model Machine', 'Brand Machine', 'Stop Production', 'Respon Mechanic', 
                'Start Production', 'Duration', 'Standar Time', 'Problem Downtime', 'Problem MM',
                'Reason Downtime', 'Action Downtime', 'Part', 'ID Mekanik', 'Name Mekanik',
                'ID Leader', 'Name Leader', 'ID GL', 'Name GL', 'ID Coord', 'Name Coord', 'Group Problem'
            ];
            
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }
            
            // Style header
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ];
            $sheet->getStyle('A1:' . $col . '1')->applyFromArray($headerStyle);
            
            // Write data
            $row = 2;
            foreach ($downtimeErp2s as $downtimeErp2) {
                $col = 'A';
                $values = [
                    $downtimeErp2->date,
                    $downtimeErp2->plant,
                    $downtimeErp2->process,
                    $downtimeErp2->line,
                    $downtimeErp2->roomName,
                    $downtimeErp2->idMachine,
                    $downtimeErp2->typeMachine,
                    $downtimeErp2->modelMachine,
                    $downtimeErp2->brandMachine,
                    $downtimeErp2->stopProduction,
                    $downtimeErp2->responMechanic,
                    $downtimeErp2->startProduction,
                    $downtimeErp2->duration,
                    $downtimeErp2->Standar_Time ?? '',
                    $downtimeErp2->problemDowntime,
                    $downtimeErp2->Problem_MM ?? '',
                    $downtimeErp2->reasonDowntime,
                    $downtimeErp2->actionDowtime,
                    $downtimeErp2->Part ?? '',
                    $downtimeErp2->idMekanik,
                    $downtimeErp2->nameMekanik,
                    $downtimeErp2->idLeader,
                    $downtimeErp2->nameLeader,
                    $downtimeErp2->idGL ?? '',
                    $downtimeErp2->nameGL ?? '',
                    $downtimeErp2->idCoord,
                    $downtimeErp2->nameCoord,
                    $downtimeErp2->groupProblem,
                ];
                
                foreach ($values as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'AB') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $filename = 'downtime_erp2_' . date('Y-m-d_His') . '.xlsx';
            
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'downtime_erp2_');
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
}
