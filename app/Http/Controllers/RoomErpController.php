<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomErp;
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
        $roomErps = RoomErp::orderBy('name', 'asc')->paginate(10);
        return view('room_erp.index', compact('roomErps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('room_erp.create');
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
        return view('room_erp.edit', compact('roomErp'));
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
        $roomErp->update($validated);
        return redirect()->route('room-erp.index')->with('success', 'Room ERP updated successfully.');
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
}
