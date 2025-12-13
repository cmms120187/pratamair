<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DowntimeErp;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DowntimeErpController extends Controller
{
    public function index(Request $request)
    {
        $query = DowntimeErp::query();
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        // Filter by plant
        if ($request->filled('plant')) {
            $query->where('plant', $request->plant);
        }
        
        // Filter by process
        if ($request->filled('process')) {
            $query->where('process', $request->process);
        }
        
        // Filter by line
        if ($request->filled('line')) {
            $query->where('line', $request->line);
        }
        
        // Filter by room
        if ($request->filled('room')) {
            $query->where('roomName', $request->room);
        }
        
        // Filter by typeMachine
        if ($request->filled('typeMachine')) {
            $query->where('typeMachine', $request->typeMachine);
        }
        
        $data = $query->orderBy('date', 'desc')->paginate(12)->withQueryString();
        
        // Get unique values for filters
        $plants = DowntimeErp::distinct()->whereNotNull('plant')->where('plant', '!=', '')->orderBy('plant')->pluck('plant')->unique();
        $processes = DowntimeErp::distinct()->whereNotNull('process')->where('process', '!=', '')->orderBy('process')->pluck('process')->unique();
        $lines = DowntimeErp::distinct()->whereNotNull('line')->where('line', '!=', '')->orderBy('line')->pluck('line')->unique();
        $rooms = DowntimeErp::distinct()->whereNotNull('roomName')->where('roomName', '!=', '')->orderBy('roomName')->pluck('roomName')->unique();
        $typeMachines = DowntimeErp::distinct()->whereNotNull('typeMachine')->where('typeMachine', '!=', '')->orderBy('typeMachine')->pluck('typeMachine')->unique();
        
        return view('downtime_erp.index', compact('data', 'plants', 'processes', 'lines', 'rooms', 'typeMachines'));
    }

    public function import(Request $request)
    {
        $file = $request->file('csv_file');
        if (!$file) {
            return back()->withErrors(['csv_file' => 'File not found']);
        }
        
        // Detect delimiter (tab or semicolon)
        $delimiter = $this->detectDelimiter($file->getRealPath());
        
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 0, $delimiter);
        
        // Check if header is valid
        if (!$header || count($header) < 2) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Invalid CSV format. Please check the file format.']);
        }
        
        // Normalize header (trim and handle "Standar Time" vs "Standar_Time")
        $header = array_map(function($h) {
            $h = trim($h);
            // Handle "Standar Time" -> "Standar_Time"
            if ($h === 'Standar Time') {
                return 'Standar_Time';
            }
            return $h;
        }, $header);
        
        $rowCount = 0;
        $errorCount = 0;
        
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            try {
                // Skip if row count doesn't match header count
                if (count($row) !== count($header)) {
                    $errorCount++;
                    continue;
                }
                
                $data = array_combine($header, $row);
                
                // Debug: Check if date exists in data
                if (!isset($data['date']) || empty(trim($data['date']))) {
                    $errorCount++;
                    continue;
                }
                
                // Filter hanya kolom yang ada di fillable
                $fillable = (new DowntimeErp())->getFillable();
                $filteredData = [];
                
                foreach ($fillable as $field) {
                    if (isset($data[$field])) {
                        $filteredData[$field] = $data[$field];
                    }
                }
                
                // Normalize date format: 2024/01/02 -> 2024-01-02
                if (isset($filteredData['date']) && !empty(trim($filteredData['date']))) {
                    // Convert from YYYY/MM/DD to YYYY-MM-DD
                    $dateStr = trim($filteredData['date']);
                    if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2})$/', $dateStr, $matches)) {
                        $filteredData['date'] = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
                    } else if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dateStr)) {
                        // Already in correct format
                        $filteredData['date'] = $dateStr;
                    } else {
                        // Try to parse using Carbon
                        try {
                            $filteredData['date'] = \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // If parsing fails, skip this row
                            $errorCount++;
                            continue;
                        }
                    }
                } else {
                    // Skip row if date is empty
                    $errorCount++;
                    continue;
                }
                
                // Clean and normalize data
                $nullableFields = ['Standar_Time', 'Problem_MM', 'Part', 'idGL', 'nameGL'];
                foreach ($filteredData as $key => $value) {
                    $value = trim($value);
                    if ($value === '' || $value === null) {
                        // Set to null for nullable fields, keep empty string for required string fields
                        if (in_array($key, $nullableFields)) {
                            $filteredData[$key] = null;
                        } else {
                            // For required string fields, use empty string
                            $filteredData[$key] = '';
                        }
                    } else {
                        $filteredData[$key] = $value;
                    }
                }
                
                // Ensure date is set before create
                if (!isset($filteredData['date']) || empty($filteredData['date'])) {
                    $errorCount++;
                    continue;
                }
                
                DowntimeErp::create($filteredData);
                $rowCount++;
            } catch (\Exception $e) {
                $errorCount++;
                // Log error but continue processing
                \Log::error('Error importing downtime row: ' . $e->getMessage(), [
                    'row' => $row ?? null,
                    'header' => $header,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        fclose($handle);
        
        $message = "Imported $rowCount rows.";
        if ($errorCount > 0) {
            $message .= " Skipped $errorCount rows with errors.";
        }
        
        return back()->with('success', $message);
    }

    public function create()
    {
        return view('downtime_erp.create');
    }

    public function show($id)
    {
        $row = DowntimeErp::findOrFail($id);
        return view('downtime_erp.show', compact('row'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'plant' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'line' => 'nullable|string|max:255',
            'roomName' => 'nullable|string|max:255',
            'idMachine' => 'nullable|string|max:255',
            'typeMachine' => 'nullable|string|max:255',
            'modelMachine' => 'nullable|string|max:255',
            'brandMachine' => 'nullable|string|max:255',
            'stopProduction' => 'nullable|string|max:255',
            'responMechanic' => 'nullable|string|max:255',
            'startProduction' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'Standar_Time' => 'nullable|string|max:255',
            'problemDowntime' => 'nullable|string|max:255',
            'Problem_MM' => 'nullable|string|max:255',
            'reasonDowntime' => 'nullable|string|max:255',
            'actionDowtime' => 'nullable|string|max:255',
            'Part' => 'nullable|string|max:255',
            'idMekanik' => 'nullable|string|max:255',
            'nameMekanik' => 'nullable|string|max:255',
            'idLeader' => 'nullable|string|max:255',
            'nameLeader' => 'nullable|string|max:255',
            'idCoord' => 'nullable|string|max:255',
            'nameCoord' => 'nullable|string|max:255',
            'groupProblem' => 'nullable|string|max:255',
        ]);

        DowntimeErp::create($validated);
        return redirect()->route('downtime_erp.index')->with('success', 'Downtime ERP created successfully.');
    }

    public function update(Request $request, $id)
    {
        $row = DowntimeErp::findOrFail($id);
        
        $validated = $request->validate([
            'date' => 'required|date',
            'plant' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'line' => 'nullable|string|max:255',
            'roomName' => 'nullable|string|max:255',
            'idMachine' => 'nullable|string|max:255',
            'typeMachine' => 'nullable|string|max:255',
            'modelMachine' => 'nullable|string|max:255',
            'brandMachine' => 'nullable|string|max:255',
            'stopProduction' => 'nullable|string|max:255',
            'responMechanic' => 'nullable|string|max:255',
            'startProduction' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'Standar_Time' => 'nullable|string|max:255',
            'problemDowntime' => 'nullable|string|max:255',
            'Problem_MM' => 'nullable|string|max:255',
            'reasonDowntime' => 'nullable|string|max:255',
            'actionDowtime' => 'nullable|string|max:255',
            'Part' => 'nullable|string|max:255',
            'idMekanik' => 'nullable|string|max:255',
            'nameMekanik' => 'nullable|string|max:255',
            'idLeader' => 'nullable|string|max:255',
            'nameLeader' => 'nullable|string|max:255',
            'idCoord' => 'nullable|string|max:255',
            'nameCoord' => 'nullable|string|max:255',
            'groupProblem' => 'nullable|string|max:255',
        ]);

        $row->update($validated);
        return redirect()->route('downtime_erp.index')->with('success', 'Downtime ERP updated successfully.');
    }

    public function destroy($id)
    {
        $row = DowntimeErp::findOrFail($id);
        $row->delete();
        return redirect()->route('downtime_erp.index')->with('success', 'Downtime ERP deleted successfully.');
    }

    public function edit($id)
    {
        $row = DowntimeErp::findOrFail($id);
        return view('downtime_erp.edit', compact('row'));
    }
    
    /**
     * Detect CSV delimiter (tab, semicolon, or comma)
     */
    private function detectDelimiter($filePath)
    {
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);
        
        $delimiters = ["\t", ";", ","];
        $delimiterCounts = [];
        
        foreach ($delimiters as $delimiter) {
            $delimiterCounts[$delimiter] = substr_count($firstLine, $delimiter);
        }
        
        // Return delimiter with highest count
        $detectedDelimiter = array_search(max($delimiterCounts), $delimiterCounts);
        
        // Default to tab if detection fails
        return $detectedDelimiter ?: "\t";
    }
    
    /**
     * Search machine by ID Machine
     */
    public function searchMachine(Request $request)
    {
        $idMachine = $request->input('idMachine');
        
        $machine = \App\Models\Machine::with(['plant', 'process', 'line', 'room', 'machineType', 'brand', 'model'])
            ->where('idMachine', $idMachine)
            ->first();
        
        if (!$machine) {
            return response()->json([
                'success' => false,
                'message' => 'Machine not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'machine' => [
                'idMachine' => $machine->idMachine,
                'typeMachine' => $machine->machineType->name ?? '-',
                'modelMachine' => $machine->model->name ?? '-',
                'brandMachine' => $machine->brand->name ?? '-',
                'roomName' => $machine->room->name ?? '-',
                'plant' => $machine->plant->name ?? '-',
                'process' => $machine->process->name ?? '-',
                'line' => $machine->line->name ?? '-',
            ]
        ]);
    }

    /**
     * Download Excel file with current data
     */
    public function download(Request $request)
    {
        try {
            // Check if user is admin
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized. Only admin can download data.');
            }
            $downtimeErps = DowntimeErp::orderBy('date', 'desc')->get();
            
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
            foreach ($downtimeErps as $downtimeErp) {
                $col = 'A';
                $values = [
                    $downtimeErp->date,
                    $downtimeErp->plant ?? '',
                    $downtimeErp->process ?? '',
                    $downtimeErp->line ?? '',
                    $downtimeErp->roomName ?? '',
                    $downtimeErp->idMachine ?? '',
                    $downtimeErp->typeMachine ?? '',
                    $downtimeErp->modelMachine ?? '',
                    $downtimeErp->brandMachine ?? '',
                    $downtimeErp->stopProduction ?? '',
                    $downtimeErp->responMechanic ?? '',
                    $downtimeErp->startProduction ?? '',
                    $downtimeErp->duration ?? '',
                    $downtimeErp->Standar_Time ?? '',
                    $downtimeErp->problemDowntime ?? '',
                    $downtimeErp->Problem_MM ?? '',
                    $downtimeErp->reasonDowntime ?? '',
                    $downtimeErp->actionDowtime ?? '',
                    $downtimeErp->Part ?? '',
                    $downtimeErp->idMekanik ?? '',
                    $downtimeErp->nameMekanik ?? '',
                    $downtimeErp->idLeader ?? '',
                    $downtimeErp->nameLeader ?? '',
                    $downtimeErp->idGL ?? '',
                    $downtimeErp->nameGL ?? '',
                    $downtimeErp->idCoord ?? '',
                    $downtimeErp->nameCoord ?? '',
                    $downtimeErp->groupProblem ?? '',
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
            
            $filename = 'downtime_erp_' . date('Y-m-d_His') . '.xlsx';
            
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'downtime_erp_');
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
                    $downtimeData = [
                        'date' => $this->parseDate($data['date'] ?? $data['Date'] ?? ''),
                        'plant' => trim($data['plant'] ?? $data['Plant'] ?? '') ?: null,
                        'process' => trim($data['process'] ?? $data['Process'] ?? '') ?: null,
                        'line' => trim($data['line'] ?? $data['Line'] ?? '') ?: null,
                        'roomName' => trim($data['roomName'] ?? $data['Room Name'] ?? $data['room_name'] ?? '') ?: null,
                        'idMachine' => trim($data['idMachine'] ?? $data['ID Machine'] ?? $data['id_machine'] ?? '') ?: null,
                        'typeMachine' => trim($data['typeMachine'] ?? $data['Type Machine'] ?? $data['type_machine'] ?? '') ?: null,
                        'modelMachine' => trim($data['modelMachine'] ?? $data['Model Machine'] ?? $data['model_machine'] ?? '') ?: null,
                        'brandMachine' => trim($data['brandMachine'] ?? $data['Brand Machine'] ?? $data['brand_machine'] ?? '') ?: null,
                        'stopProduction' => trim($data['stopProduction'] ?? $data['Stop Production'] ?? $data['stop_production'] ?? '') ?: null,
                        'responMechanic' => trim($data['responMechanic'] ?? $data['Respon Mechanic'] ?? $data['respon_mechanic'] ?? '') ?: null,
                        'startProduction' => trim($data['startProduction'] ?? $data['Start Production'] ?? $data['start_production'] ?? '') ?: null,
                        'duration' => trim($data['duration'] ?? $data['Duration'] ?? '') ?: null,
                        'Standar_Time' => trim($data['Standar_Time'] ?? $data['Standar Time'] ?? $data['standar_time'] ?? '') ?: null,
                        'problemDowntime' => trim($data['problemDowntime'] ?? $data['Problem Downtime'] ?? $data['problem_downtime'] ?? '') ?: null,
                        'Problem_MM' => trim($data['Problem_MM'] ?? $data['Problem MM'] ?? $data['problem_mm'] ?? '') ?: null,
                        'reasonDowntime' => trim($data['reasonDowntime'] ?? $data['Reason Downtime'] ?? $data['reason_downtime'] ?? '') ?: null,
                        'actionDowtime' => trim($data['actionDowtime'] ?? $data['Action Downtime'] ?? $data['action_downtime'] ?? '') ?: null,
                        'Part' => trim($data['Part'] ?? $data['part'] ?? '') ?: null,
                        'idMekanik' => trim($data['idMekanik'] ?? $data['ID Mekanik'] ?? $data['id_mekanik'] ?? '') ?: null,
                        'nameMekanik' => trim($data['nameMekanik'] ?? $data['Name Mekanik'] ?? $data['name_mekanik'] ?? '') ?: null,
                        'idLeader' => trim($data['idLeader'] ?? $data['ID Leader'] ?? $data['id_leader'] ?? '') ?: null,
                        'nameLeader' => trim($data['nameLeader'] ?? $data['Name Leader'] ?? $data['name_leader'] ?? '') ?: null,
                        'idGL' => trim($data['idGL'] ?? $data['ID GL'] ?? $data['id_gl'] ?? '') ?: null,
                        'nameGL' => trim($data['nameGL'] ?? $data['Name GL'] ?? $data['name_gl'] ?? '') ?: null,
                        'idCoord' => trim($data['idCoord'] ?? $data['ID Coord'] ?? $data['id_coord'] ?? '') ?: null,
                        'nameCoord' => trim($data['nameCoord'] ?? $data['Name Coord'] ?? $data['name_coord'] ?? '') ?: null,
                        'groupProblem' => trim($data['groupProblem'] ?? $data['Group Problem'] ?? $data['group_problem'] ?? '') ?: null,
                    ];
                    
                    // Validate required fields
                    if (empty($downtimeData['date'])) {
                        $errorCount++;
                        continue;
                    }
                    
                    DowntimeErp::create($downtimeData);
                    $rowCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Error importing downtime ERP row: ' . $e->getMessage(), [
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
