<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartErp;
use App\Models\System;
use App\Models\MachineType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PartErpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $partErps = PartErp::with(['system', 'machineTypes'])->orderBy('part_number', 'asc')->paginate(10);
        return view('part_erp.index', compact('partErps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $systems = System::orderBy('nama_sistem', 'asc')->get();
        $machineTypes = MachineType::orderBy('name', 'asc')->get();
        return view('part_erp.create', compact('systems', 'machineTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'system_id' => 'nullable|exists:systems,id',
            'brand' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'stock' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'machine_type_ids' => 'nullable|array',
            'machine_type_ids.*' => 'exists:machine_types,id',
        ]);

        $partData = [
            'part_number' => $validated['part_number'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'brand' => $validated['brand'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'stock' => $validated['stock'] ?? 0,
            'price' => $validated['price'] ?? null,
        ];

        // Set category based on selected system
        if ($request->filled('system_id')) {
            $system = System::find($request->system_id);
            $partData['category'] = $system->nama_sistem;
        } else {
            $partData['category'] = null;
        }

        $partErp = PartErp::create($partData);
        
        // Sync machine types (location)
        if ($request->has('machine_type_ids')) {
            $partErp->machineTypes()->sync($request->machine_type_ids);
        }

        return redirect()->route('part-erp.index')->with('success', 'Part ERP created successfully.');
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
        $partErp = PartErp::with(['system', 'machineTypes'])->findOrFail($id);
        $systems = System::orderBy('nama_sistem', 'asc')->get();
        $machineTypes = MachineType::orderBy('name', 'asc')->get();
        $page = $request->query('page', 1);
        return view('part_erp.edit', compact('partErp', 'systems', 'machineTypes', 'page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'system_id' => 'nullable|exists:systems,id',
            'brand' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'stock' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'machine_type_ids' => 'nullable|array',
            'machine_type_ids.*' => 'exists:machine_types,id',
        ]);

        $partErp = PartErp::findOrFail($id);
        
        $partData = [
            'part_number' => $validated['part_number'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'brand' => $validated['brand'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'stock' => $validated['stock'] ?? 0,
            'price' => $validated['price'] ?? null,
        ];

        // Set category based on selected system
        if ($request->filled('system_id')) {
            $system = System::find($request->system_id);
            $partData['category'] = $system->nama_sistem;
        } else {
            $partData['category'] = null;
        }

        $partErp->update($partData);
        
        // Sync machine types (location)
        if ($request->has('machine_type_ids')) {
            $partErp->machineTypes()->sync($request->machine_type_ids);
        } else {
            $partErp->machineTypes()->sync([]);
        }

        // Get page from request or default to 1
        $page = $request->input('page', 1);
        
        return redirect()->route('part-erp.index', ['page' => $page])->with('success', 'Part ERP updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $partErp = PartErp::findOrFail($id);
        $partErp->delete();
        return redirect()->route('part-erp.index')->with('success', 'Part ERP deleted successfully.');
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
                    $partData = [
                        'part_number' => trim($data['part_number'] ?? $data['Part Number'] ?? $data['partNumber'] ?? ''),
                        'name' => trim($data['name'] ?? $data['Name'] ?? ''),
                        'description' => trim($data['description'] ?? $data['Description'] ?? '') ?: null,
                        'brand' => trim($data['brand'] ?? $data['Brand'] ?? '') ?: null,
                        'unit' => trim($data['unit'] ?? $data['Unit'] ?? '') ?: null,
                        'stock' => !empty(trim($data['stock'] ?? $data['Stock'] ?? '')) ? (int)trim($data['stock'] ?? $data['Stock'] ?? '') : 0,
                        'price' => !empty(trim($data['price'] ?? $data['Price'] ?? '')) ? (float)trim($data['price'] ?? $data['Price'] ?? '') : null,
                    ];
                    
                    // Handle category (System) - find by nama_sistem or ID
                    $categoryName = trim($data['Category (System)'] ?? $data['Category'] ?? $data['category'] ?? '');
                    if (!empty($categoryName)) {
                        // Try to find by nama_sistem first
                        $system = System::where('nama_sistem', $categoryName)->first();
                        // If not found and categoryName is numeric, try to find by ID
                        if (!$system && is_numeric($categoryName)) {
                            $system = System::find($categoryName);
                        }
                        if ($system) {
                            $partData['category'] = $system->nama_sistem; // Store nama_sistem, not ID
                        } else {
                            $partData['category'] = $categoryName; // Store as is if not found
                        }
                    } else {
                        $partData['category'] = null;
                    }
                    
                    // Validate required fields
                    if (empty($partData['part_number']) || empty($partData['name'])) {
                        $errorCount++;
                        continue;
                    }
                    
                    $partErp = PartErp::create($partData);
                    
                    // Handle location (Machine Types) - find by name (comma-separated)
                    $locationNames = trim($data['location'] ?? $data['Location'] ?? $data['Machine Type'] ?? $data['machine_type'] ?? '');
                    if (!empty($locationNames)) {
                        $machineTypeNames = array_map('trim', explode(',', $locationNames));
                        $machineTypeIds = [];
                        foreach ($machineTypeNames as $mtName) {
                            $machineType = MachineType::where('name', $mtName)->first();
                            if ($machineType) {
                                $machineTypeIds[] = $machineType->id;
                            }
                        }
                        if (!empty($machineTypeIds)) {
                            $partErp->machineTypes()->sync($machineTypeIds);
                        }
                    }
                    $rowCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Error importing part ERP row: ' . $e->getMessage(), [
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
            $partErps = PartErp::with(['system', 'machineTypes'])->orderBy('part_number', 'asc')->get();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'Part Number');
            $sheet->setCellValue('B1', 'Name');
            $sheet->setCellValue('C1', 'Description');
            $sheet->setCellValue('D1', 'Category');
            $sheet->setCellValue('E1', 'Brand');
            $sheet->setCellValue('F1', 'Unit');
            $sheet->setCellValue('G1', 'Stock');
            $sheet->setCellValue('H1', 'Price');
            $sheet->setCellValue('I1', 'Location');
            
            // Style header
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ];
            $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
            
            // Write data
            $row = 2;
            foreach ($partErps as $partErp) {
                $sheet->setCellValue('A' . $row, $partErp->part_number);
                $sheet->setCellValue('B' . $row, $partErp->name);
                $sheet->setCellValue('C' . $row, $partErp->description ?? '');
                // Category (System)
                $categoryName = '';
                if ($partErp->category) {
                    // Try to find system by nama_sistem first (since category stores nama_sistem)
                    $system = System::where('nama_sistem', $partErp->category)->first();
                    // If not found and category is numeric, try to find by ID
                    if (!$system && is_numeric($partErp->category)) {
                        $system = System::find($partErp->category);
                    }
                    $categoryName = $system ? $system->nama_sistem : $partErp->category;
                }
                $sheet->setCellValue('D' . $row, $categoryName);
                $sheet->setCellValue('E' . $row, $partErp->brand ?? '');
                $sheet->setCellValue('F' . $row, $partErp->unit ?? '');
                $sheet->setCellValue('G' . $row, $partErp->stock ?? 0);
                $sheet->setCellValue('H' . $row, $partErp->price ?? '');
                // Location (Machine Types) - comma separated
                $machineTypeNames = $partErp->machineTypes->pluck('name')->toArray();
                $sheet->setCellValue('I' . $row, implode(', ', $machineTypeNames));
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $filename = 'part_erp_' . date('Y-m-d_His') . '.xlsx';
            
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'part_erp_');
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
