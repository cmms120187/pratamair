<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DowntimeErp;

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
}
