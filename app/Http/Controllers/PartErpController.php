<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartErp;
use App\Models\PartErpStockMovement;
use App\Models\System;
use App\Models\MachineType;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\WorkOrder;
use App\Services\SparepartLowStockService;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PartErpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PartErp::with(['system', 'machineTypes']);

        // Search by part_number or name
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('part_number', 'like', '%' . $term . '%')
                  ->orWhere('name', 'like', '%' . $term . '%');
            });
        }

        // Filter by system (category)
        if ($request->filled('system_id')) {
            $system = System::find($request->system_id);
            if ($system) {
                $query->where('category', $system->nama_sistem);
            }
        }

        // Filter low stock only
        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<', 'minimum_stock')
                  ->where('minimum_stock', '>', 0);
        }

        $sortBy = $request->get('sort_by', 'part_number');
        $sortDir = $request->get('sort_dir', 'asc');
        if (!in_array($sortBy, ['part_number', 'name', 'stock', 'price', 'category'])) {
            $sortBy = 'part_number';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }
        $query->orderBy($sortBy, $sortDir);

        $partErps = $query->paginate(15)->withQueryString();
        $systems = System::orderBy('nama_sistem', 'asc')->get();

        return view('part_erp.index', compact('partErps', 'systems'));
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
            'minimum_stock' => 'nullable|integer|min:0',
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
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
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

        // Check and send low stock alert
        $lowStockService = new SparepartLowStockService();
        $lowStockService->checkAndSendAlerts($partErp);

        return redirect()->route('part-erp.index')->with('success', 'Part ERP created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $partErp = PartErp::with(['system', 'machineTypes', 'stockMovements' => fn ($q) => $q->with('user')->limit(50)])->findOrFail($id);
        $recentDowntimeErp2 = DowntimeErp2::orderBy('date', 'desc')->orderBy('id', 'desc')->limit(100)->get(['id', 'date', 'idMachine', 'Part']);
        $recentDowntimeErp = DowntimeErp::orderBy('date', 'desc')->orderBy('id', 'desc')->limit(100)->get(['id', 'date', 'idMachine', 'Part']);
        $recentPmExecutions = PreventiveMaintenanceExecution::with('schedule')->orderBy('scheduled_date', 'desc')->orderBy('id', 'desc')->limit(100)->get();
        $recentWorkOrders = WorkOrder::orderBy('created_at', 'desc')->limit(100)->get(['id', 'wo_number', 'description', 'status', 'created_at']);
        return view('part_erp.show', compact('partErp', 'recentDowntimeErp2', 'recentDowntimeErp', 'recentPmExecutions', 'recentWorkOrders'));
    }

    /**
     * Add or reduce stock with document (MR/PO/MO) or reference (downtime/PM/work order).
     */
    public function storeStockMovement(Request $request, string $id)
    {
        $rules = [
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'reference_type' => 'nullable|string|in:manual,downtime_erp2,downtime_erp,preventive_maintenance_execution,work_order,other',
            'reference_id' => 'nullable|integer|min:1',
        ];
        $referenceType = $request->input('reference_type', 'manual');
        if ($referenceType === 'manual' || !$referenceType) {
            $rules['document_type'] = 'required|in:MR,PO,MO';
            $rules['document_number'] = 'required|string|max:255';
        } elseif ($referenceType !== 'other') {
            $rules['reference_id'] = 'required|integer|min:1';
        }
        $validated = $request->validate($rules);

        $partErp = PartErp::findOrFail($id);
        $currentStock = (int) $partErp->stock;
        $qty = (int) $validated['quantity'];

        if ($validated['type'] === 'out') {
            if ($qty > $currentStock) {
                return redirect()->back()->with('error', 'Jumlah keluar tidak boleh melebihi stok saat ini (' . $currentStock . ').');
            }
            $quantitySigned = -$qty;
            $balanceAfter = $currentStock - $qty;
        } else {
            $quantitySigned = $qty;
            $balanceAfter = $currentStock + $qty;
        }

        $docType = $validated['document_type'] ?? null;
        $docNumber = isset($validated['document_number']) ? trim($validated['document_number']) : null;
        if ($referenceType && $referenceType !== 'manual') {
            if ($referenceType === 'downtime_erp2') {
                $ref = DowntimeErp2::find($validated['reference_id'] ?? 0);
                if (!$ref) {
                    return redirect()->back()->with('error', 'Downtime ERP2 tidak ditemukan.');
                }
                if (!$docNumber) {
                    $docNumber = 'DT2-' . $ref->id;
                }
            } elseif ($referenceType === 'downtime_erp') {
                $ref = DowntimeErp::find($validated['reference_id'] ?? 0);
                if (!$ref) {
                    return redirect()->back()->with('error', 'Downtime ERP tidak ditemukan.');
                }
                if (!$docNumber) {
                    $docNumber = 'DT-' . $ref->id;
                }
            } elseif ($referenceType === 'preventive_maintenance_execution') {
                $ref = PreventiveMaintenanceExecution::find($validated['reference_id'] ?? 0);
                if (!$ref) {
                    return redirect()->back()->with('error', 'Preventive Maintenance Execution tidak ditemukan.');
                }
                if (!$docNumber) {
                    $docNumber = 'PM-' . $ref->id;
                }
            } elseif ($referenceType === 'work_order') {
                $ref = WorkOrder::find($validated['reference_id'] ?? 0);
                if (!$ref) {
                    return redirect()->back()->with('error', 'Work Order tidak ditemukan.');
                }
                if (!$docNumber) {
                    $docNumber = 'WO-' . $ref->id;
                }
            }
        }

        DB::beginTransaction();
        try {
            PartErpStockMovement::create([
                'part_erp_id' => $partErp->id,
                'type' => $validated['type'],
                'document_type' => $docType,
                'document_number' => $docNumber,
                'quantity' => $quantitySigned,
                'balance_after' => $balanceAfter,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
                'reference_type' => $referenceType && $referenceType !== 'manual' ? $referenceType : null,
                'reference_id' => ($referenceType && $referenceType !== 'manual' && !empty($validated['reference_id'])) ? $validated['reference_id'] : null,
            ]);

            $partErp->update(['stock' => $balanceAfter]);

            $lowStockService = new SparepartLowStockService();
            $lowStockService->checkAndSendAlerts($partErp->fresh());

            DB::commit();

            $typeLabel = $validated['type'] === 'in' ? 'Tambah' : 'Kurangi';
            $refLabel = $referenceType && $referenceType !== 'manual' ? " (relasi: {$referenceType} #" . ($validated['reference_id'] ?? '') . ')' : " {$docType} #{$docNumber}";
            return redirect()->back()->with('success', "Stok berhasil di{$typeLabel}{$refLabel}. Stok sekarang: {$balanceAfter}");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Part ERP store stock movement: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan pergerakan stok: ' . $e->getMessage());
        }
    }

    /**
     * Report: part usage / stock movements with relation to downtime, PM, work order.
     */
    public function stockMovementReport(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        // Default to current month when no date filter
        if (!$dateFrom && !$dateTo) {
            $dateFrom = now()->startOfMonth()->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        $query = PartErpStockMovement::with(['partErp', 'user'])
            ->orderBy('created_at', 'desc');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($request->filled('part_erp_id')) {
            $query->where('part_erp_id', $request->part_erp_id);
        }
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }
        if ($request->filled('type')) {
            if ($request->type === 'out') {
                $query->where('quantity', '<', 0);
            } elseif ($request->type === 'in') {
                $query->where('quantity', '>', 0);
            }
        }

        // Summary totals (same filters, no pagination)
        $summaryQuery = PartErpStockMovement::query();
        if ($dateFrom) $summaryQuery->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $summaryQuery->whereDate('created_at', '<=', $dateTo);
        if ($request->filled('part_erp_id')) $summaryQuery->where('part_erp_id', $request->part_erp_id);
        if ($request->filled('reference_type')) $summaryQuery->where('reference_type', $request->reference_type);
        if ($request->filled('type')) {
            if ($request->type === 'out') $summaryQuery->where('quantity', '<', 0);
            elseif ($request->type === 'in') $summaryQuery->where('quantity', '>', 0);
        }
        $totalMasuk = (clone $summaryQuery)->where('quantity', '>', 0)->sum('quantity');
        $totalKeluar = abs((clone $summaryQuery)->where('quantity', '<', 0)->sum('quantity'));
        $summary = ['total_masuk' => $totalMasuk, 'total_keluar' => $totalKeluar];

        $movements = $query->paginate(25)->withQueryString();
        $parts = PartErp::orderBy('name')->get(['id', 'part_number', 'name']);
        return view('part_erp.stock_movement_report', compact('movements', 'parts', 'summary', 'dateFrom', 'dateTo'));
    }

    /**
     * Export stock movement report to Excel.
     */
    public function stockMovementReportExport(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if (!$dateFrom && !$dateTo) {
            $dateFrom = now()->startOfMonth()->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        $query = PartErpStockMovement::with(['partErp', 'user'])
            ->orderBy('created_at', 'desc');
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        if ($request->filled('part_erp_id')) $query->where('part_erp_id', $request->part_erp_id);
        if ($request->filled('reference_type')) $query->where('reference_type', $request->reference_type);
        if ($request->filled('type')) {
            if ($request->type === 'out') $query->where('quantity', '<', 0);
            elseif ($request->type === 'in') $query->where('quantity', '>', 0);
        }
        $movements = $query->limit(10000)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penggunaan Part');

        $headers = ['Tanggal', 'Part Number', 'Nama Part', 'Tipe', 'Dokumen/Relasi', 'Qty', 'Stok Akhir', 'User'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
        ]);

        $row = 2;
        foreach ($movements as $m) {
            $refLabel = $m->reference_type && $m->reference_id
                ? $m->getReferenceLabel()
                : ($m->document_type ?? '-') . ' #' . ($m->document_number ?? '-');
            $tipe = $m->quantity > 0 ? 'Masuk' : 'Keluar';
            $sheet->fromArray([
                $m->created_at->format('d/m/Y H:i'),
                $m->partErp->part_number ?? '-',
                $m->partErp->name ?? '-',
                $tipe,
                $refLabel,
                $m->quantity > 0 ? '+' . $m->quantity : $m->quantity,
                $m->balance_after ?? '-',
                $m->user->name ?? '-',
            ], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Penggunaan_Part_' . now()->format('Y-m-d_His') . '.xlsx';
        $tempFile = sys_get_temp_dir() . '/part_report_' . uniqid() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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
            'minimum_stock' => 'nullable|integer|min:0',
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
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
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

        // Check and send low stock alert
        $lowStockService = new SparepartLowStockService();
        $lowStockService->checkAndSendAlerts($partErp->fresh());

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
                        'minimum_stock' => !empty(trim($data['minimum_stock'] ?? $data['Minimum Stock'] ?? $data['minimumStock'] ?? '')) ? (int)trim($data['minimum_stock'] ?? $data['Minimum Stock'] ?? $data['minimumStock'] ?? '') : 0,
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
            $sheet->setCellValue('D1', 'Category (System)');
            $sheet->setCellValue('E1', 'Brand');
            $sheet->setCellValue('F1', 'Unit');
            $sheet->setCellValue('G1', 'Stock');
            $sheet->setCellValue('H1', 'Minimum Stock');
            $sheet->setCellValue('I1', 'Price');
            $sheet->setCellValue('J1', 'Location');
            
            // Style header
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ];
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
            
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
                $sheet->setCellValue('H' . $row, $partErp->minimum_stock ?? 0);
                $sheet->setCellValue('I' . $row, $partErp->price ?? '');
                // Location (Machine Types) - comma separated
                $machineTypeNames = $partErp->machineTypes->pluck('name')->toArray();
                $sheet->setCellValue('J' . $row, implode(', ', $machineTypeNames));
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'J') as $col) {
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

    /**
     * Preview Part ERP data from downtime tables before extraction.
     */
    public function previewFromDowntime(Request $request)
    {
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only admin can access this feature.'], 403);
        }
        $dataSource = $request->input('data_source');
        try {
            $uniqueParts = [];
            $existingNames = PartErp::pluck('name')->map(fn ($n) => strtolower(trim($n)))->toArray();
            if ($dataSource === 'downtime_erp') {
                $rows = DowntimeErp::select('Part')->whereNotNull('Part')->where('Part', '!=', '')->distinct()->get();
                foreach ($rows as $r) {
                    $name = trim($r->Part);
                    if ($name === '') continue;
                    $key = strtolower($name);
                    if (!isset($uniqueParts[$key])) $uniqueParts[$key] = ['name' => $name];
                }
            } elseif ($dataSource === 'downtime_erp2') {
                $rows = DowntimeErp2::select('Part')->whereNotNull('Part')->where('Part', '!=', '')->distinct()->get();
                foreach ($rows as $r) {
                    $name = trim($r->Part);
                    if ($name === '') continue;
                    $key = strtolower($name);
                    if (!isset($uniqueParts[$key])) $uniqueParts[$key] = ['name' => $name];
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid data source'], 400);
            }
            $totalUnique = count($uniqueParts);
            $newCount = 0;
            $existingCount = 0;
            $sampleData = [];
            foreach ($uniqueParts as $p) {
                if (in_array(strtolower(trim($p['name'])), $existingNames)) {
                    $existingCount++;
                } else {
                    $newCount++;
                    if (count($sampleData) < 20) $sampleData[] = $p;
                }
            }
            return response()->json([
                'success' => true,
                'total_unique' => $totalUnique,
                'new_count' => $newCount,
                'existing_count' => $existingCount,
                'sample_data' => array_values($sampleData),
            ]);
        } catch (\Exception $e) {
            \Log::error('Part ERP preview from downtime: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Extract unique Part ERP from downtime tables.
     */
    public function extractFromDowntime(Request $request)
    {
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('part-erp.index')->with('error', 'Unauthorized. Only admin can access this feature.');
        }
        $dataSource = $request->input('data_source');
        DB::beginTransaction();
        try {
            $uniqueParts = [];
            if ($dataSource === 'downtime_erp') {
                $rows = DowntimeErp::select('Part')->whereNotNull('Part')->where('Part', '!=', '')->distinct()->get();
                foreach ($rows as $r) {
                    $name = trim($r->Part);
                    if ($name === '') continue;
                    $uniqueParts[strtolower($name)] = $name;
                }
            } elseif ($dataSource === 'downtime_erp2') {
                $rows = DowntimeErp2::select('Part')->whereNotNull('Part')->where('Part', '!=', '')->distinct()->get();
                foreach ($rows as $r) {
                    $name = trim($r->Part);
                    if ($name === '') continue;
                    $uniqueParts[strtolower($name)] = $name;
                }
            } else {
                return redirect()->route('part-erp.index')->with('error', 'Invalid data source.');
            }
            $created = 0;
            $skipped = 0;
            $errors = [];
            foreach ($uniqueParts as $name) {
                try {
                    $exists = PartErp::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->exists();
                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                    $partNumber = 'PART-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 12));
                    $counter = 0;
                    while (PartErp::where('part_number', $partNumber)->exists()) {
                        $counter++;
                        $partNumber = 'PART-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 8)) . '-' . $counter;
                    }
                    PartErp::create([
                        'part_number' => $partNumber,
                        'name' => $name,
                        'description' => null,
                        'category' => null,
                        'brand' => null,
                        'unit' => null,
                        'stock' => 0,
                        'minimum_stock' => 0,
                        'price' => null,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = $name . ': ' . $e->getMessage();
                }
            }
            DB::commit();
            return redirect()->route('part-erp.index')
                ->with('success', "Extraction completed. Created: {$created}, Skipped: {$skipped}.")
                ->with('extraction_details', ['created' => $created, 'skipped' => $skipped, 'errors' => $errors]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Part ERP extract from downtime: ' . $e->getMessage());
            return redirect()->route('part-erp.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
