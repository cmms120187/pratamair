<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionHourly;
use App\Models\ProductionDailyGrade;
use App\Models\Line;
use App\Models\Process;

class ProductionHourlyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductionHourly::with(['line', 'process']);

        // Filter by date if provided
        if ($request->filled('date')) {
            $query->whereDate('production_date', $request->date);
        }

        // Filter by line if provided
        if ($request->filled('line_id')) {
            $query->where('line_id', $request->line_id);
        }

        // Filter by process if provided
        if ($request->filled('process_id')) {
            $query->where('process_id', $request->process_id);
        }

        // Hanya tampilkan record yang memiliki hasil produksi (total_production tidak kosong)
        $query->whereNotNull('total_production')
            ->where('total_production', '!=', '');

        // Get all data untuk diakumulasi
        $allData = $query->orderBy('production_date', 'desc')
            ->orderBy('line_id', 'asc')
            ->orderBy('hour', 'asc')
            ->get();

        // Kelompokkan per tanggal dan line, lalu hitung akumulasi
        $groupedData = [];
        foreach ($allData as $item) {
            $key = $item->production_date->format('Y-m-d') . '_' . $item->line_id . '_' . $item->process_id;
            
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'production_date' => $item->production_date,
                    'line' => $item->line,
                    'process' => $item->process,
                    'line_id' => $item->line_id,
                    'process_id' => $item->process_id,
                    'total_production' => 0,
                    'total_target' => 0,
                    'hour_count' => 0, // Jumlah jam yang valid (tidak termasuk istirahat)
                    'target_production' => 0, // Target dikali jumlah jam yang valid
                    'hours' => []
                ];
            }

            // Hitung total produksi (handle string "(istirahat)" dan angka)
            $production = $item->total_production;
            $isIstirahat = ($production === '(istirahat)');
            
            if (!$isIstirahat && is_numeric($production)) {
                $groupedData[$key]['total_production'] += (int)$production;
                $groupedData[$key]['hour_count']++; // Hanya hitung jam yang tidak istirahat
                
                // Hitung target produksi (target per jam * jumlah jam yang valid)
                if ($item->target_per_hour) {
                    $groupedData[$key]['target_production'] += $item->target_per_hour;
                }
            }

            // Hitung total target (semua target, termasuk yang istirahat)
            if ($item->target_per_hour) {
                $groupedData[$key]['total_target'] += $item->target_per_hour;
            }

            $groupedData[$key]['hours'][] = $item;
        }

        // Get daily grades untuk setiap kombinasi tanggal, line, process
        foreach ($groupedData as $key => &$data) {
            $dailyGrade = ProductionDailyGrade::where('line_id', $data['line_id'])
                ->where('process_id', $data['process_id'])
                ->whereDate('production_date', $data['production_date'])
                ->first();
            
            $data['grade_b'] = $dailyGrade->grade_b ?? 0;
            $data['grade_c'] = $dailyGrade->grade_c ?? 0;
            // Yang diinput per jam adalah Grade A, jadi total_production = Grade A
            $data['grade_a'] = $data['total_production'];
            // Total Produksi = Grade A + Grade B + Grade C
            $data['total_production'] = $data['grade_a'] + $data['grade_b'] + $data['grade_c'];
        }

        // Convert to collection dan paginate manually
        $collection = collect($groupedData)->values();
        $page = $request->get('page', 1);
        $perPage = 50;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $items = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $productionHourly = new \Illuminate\Pagination\LengthAwarePaginator($items, $collection->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        // Get lines and processes for filters
        $lines = Line::with('process')->orderBy('name', 'asc')->get();
        $processes = Process::orderBy('name', 'asc')->get();

        return view('production_hourly.index', compact('productionHourly', 'lines', 'processes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get lines with Process "Production" only
        $lines = Line::with('process')
            ->whereHas('process', function($query) {
                $query->where('name', 'Production');
            })
            ->orderBy('name', 'asc')
            ->get();
        
        // Get only Production process
        $processes = Process::where('name', 'Production')->orderBy('name', 'asc')->get();

        return view('production_hourly.create', compact('lines', 'processes'));
    }

    /**
     * Show the form for creating multiple hours at once.
     */
    public function createBulk()
    {
        // Get lines with Process "Production" only
        $lines = Line::with('process')
            ->whereHas('process', function($query) {
                $query->where('name', 'Production');
            })
            ->orderBy('name', 'asc')
            ->get();
        
        // Get only Production process
        $processes = Process::where('name', 'Production')->orderBy('name', 'asc')->get();

        return view('production_hourly.create_bulk', compact('lines', 'processes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if this is bulk store (has hours array and no single hour field)
        if ($request->has('hours') && is_array($request->hours) && !$request->has('hour')) {
            return $this->storeBulk($request);
        }

        $validated = $request->validate([
            'line_id' => 'required|exists:lines,id',
            'process_id' => 'required|exists:processes,id',
            'production_date' => 'required|date',
            'hour' => 'required|integer|min:0|max:23',
            'target_per_hour' => 'nullable|integer|min:0',
            'total_production' => 'nullable|string', // Can be number or "(istirahat)" - nullable karena jika kosong berarti tidak ada kegiatan produksi
            'grade_b' => 'nullable|integer|min:0', // Per hari
            'grade_c' => 'nullable|integer|min:0', // Per hari
            'notes' => 'nullable|string',
        ]);

        // Jika total_production kosong, berarti tidak ada kegiatan produksi, tidak perlu simpan
        if (empty($validated['total_production']) || $validated['total_production'] === '') {
            // Tapi tetap simpan daily grades jika ada
            if ($request->filled('grade_b') || $request->filled('grade_c')) {
                ProductionDailyGrade::updateOrCreate(
                    [
                        'line_id' => $validated['line_id'],
                        'process_id' => $validated['process_id'],
                        'production_date' => $validated['production_date'],
                    ],
                    [
                        'grade_b' => $validated['grade_b'] ?? 0,
                        'grade_c' => $validated['grade_c'] ?? 0,
                    ]
                );
            }
            return redirect()->route('production-hourly.index')->with('success', 'Tidak ada kegiatan produksi untuk jam ini, data tidak disimpan.');
        }

        // Check if record already exists for this line, process, date, and hour
        $existing = ProductionHourly::where('line_id', $validated['line_id'])
            ->where('process_id', $validated['process_id'])
            ->whereDate('production_date', $validated['production_date'])
            ->where('hour', $validated['hour'])
            ->first();

        if ($existing) {
            return back()->withErrors(['hour' => 'Data produksi untuk Line, Process, Tanggal, dan Jam ini sudah ada. Silakan edit data yang sudah ada.'])->withInput();
        }

        // Handle daily grades (grade_b and grade_c per day)
        if ($request->filled('grade_b') || $request->filled('grade_c')) {
            $dailyGrade = ProductionDailyGrade::updateOrCreate(
                [
                    'line_id' => $validated['line_id'],
                    'process_id' => $validated['process_id'],
                    'production_date' => $validated['production_date'],
                ],
                [
                    'grade_b' => $validated['grade_b'] ?? 0,
                    'grade_c' => $validated['grade_c'] ?? 0,
                ]
            );
        }

        // Remove grade_b and grade_c from validated before creating hourly record
        unset($validated['grade_b'], $validated['grade_c']);

        ProductionHourly::create($validated);

        return redirect()->route('production-hourly.index')->with('success', 'Data produksi per jam berhasil ditambahkan.');
    }

    /**
     * Store multiple hours at once.
     */
    private function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'line_id' => 'required|exists:lines,id',
            'process_id' => 'required|exists:processes,id',
            'production_date' => 'required|date',
            'hours' => 'required|array',
            'hours.*.hour' => 'required|integer|min:0|max:23',
            'hours.*.target_per_hour' => 'nullable|integer|min:0',
            'hours.*.total_production' => 'nullable|string', // Can be number or "(istirahat)" - nullable karena jika kosong berarti tidak ada kegiatan produksi
            'grade_b' => 'nullable|integer|min:0', // Per hari
            'grade_c' => 'nullable|integer|min:0', // Per hari
            'notes' => 'nullable|string',
        ]);

        // Debug: Log jumlah hours yang diterima
        \Log::info('StoreBulk - Hours count: ' . count($validated['hours']));

        // Handle daily grades (grade_b and grade_c per day) - once for all hours
        if ($request->filled('grade_b') || $request->filled('grade_c')) {
            ProductionDailyGrade::updateOrCreate(
                [
                    'line_id' => $validated['line_id'],
                    'process_id' => $validated['process_id'],
                    'production_date' => $validated['production_date'],
                ],
                [
                    'grade_b' => $validated['grade_b'] ?? 0,
                    'grade_c' => $validated['grade_c'] ?? 0,
                ]
            );
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($validated['hours'] as $index => $hourData) {
            // Skip if total_production is empty or null
            // Periksa apakah benar-benar kosong (bukan 0 atau "(istirahat)")
            $totalProduction = isset($hourData['total_production']) ? trim($hourData['total_production']) : '';
            
            if ($totalProduction === '' || $totalProduction === null) {
                \Log::info("Skipping hour {$hourData['hour']}: total_production is empty");
                continue;
            }
            
            \Log::info("Processing hour {$hourData['hour']}: total_production = '{$totalProduction}'");

            // Check if record already exists
            $existing = ProductionHourly::where('line_id', $validated['line_id'])
                ->where('process_id', $validated['process_id'])
                ->whereDate('production_date', $validated['production_date'])
                ->where('hour', $hourData['hour'])
                ->first();

            if ($existing) {
                // Jika data sudah ada dan sudah punya total_production, skip
                if (!empty($existing->total_production) && trim($existing->total_production) !== '') {
                    $skipped++;
                    \Log::info("Skipping hour {$hourData['hour']}: record already exists with total_production");
                    continue;
                }
                // Jika data sudah ada tapi total_production kosong, update
                try {
                    $existing->update([
                        'target_per_hour' => $hourData['target_per_hour'] ?? $existing->target_per_hour,
                        'total_production' => $totalProduction,
                        'notes' => $validated['notes'] ?? $existing->notes,
                    ]);
                    $created++;
                    \Log::info("Updated existing production hourly for hour {$hourData['hour']} with total_production");
                } catch (\Exception $e) {
                    \Log::error("Error updating production hourly for hour {$hourData['hour']}: " . $e->getMessage());
                    $errors[] = "Jam {$hourData['hour']}: " . $e->getMessage();
                }
            } else {
                // Data belum ada, create baru
                try {
                    ProductionHourly::create([
                        'line_id' => $validated['line_id'],
                        'process_id' => $validated['process_id'],
                        'production_date' => $validated['production_date'],
                        'hour' => $hourData['hour'],
                        'target_per_hour' => $hourData['target_per_hour'] ?? null,
                        'total_production' => $totalProduction,
                        'notes' => $validated['notes'] ?? null,
                    ]);
                    $created++;
                    \Log::info("Successfully created production hourly for hour {$hourData['hour']}");
                } catch (\Exception $e) {
                    \Log::error("Error creating production hourly for hour {$hourData['hour']}: " . $e->getMessage());
                    $errors[] = "Jam {$hourData['hour']}: " . $e->getMessage();
                }
            }
        }

        // Jika tidak ada data yang dibuat dan tidak ada yang di-skip, berarti semua kosong
        if ($created === 0 && $skipped === 0) {
            // Tapi tetap simpan daily grades jika ada
            if ($request->filled('grade_b') || $request->filled('grade_c')) {
                // Daily grades sudah disimpan di atas
            }
            return redirect()->route('production-hourly.index')->with('info', 'Tidak ada data produksi yang diisi. Data tidak disimpan.');
        }

        $message = "Berhasil menambahkan {$created} data produksi.";
        if ($skipped > 0) {
            $message .= " {$skipped} data dilewati (sudah ada).";
        }
        if (!empty($errors)) {
            return back()->withErrors(['hours' => $errors])->withInput();
        }

        return redirect()->route('production-hourly.index')->with('success', $message);
    }

    /**
     * Display the specified resource (detail per jam untuk tanggal dan line tertentu).
     */
    public function show(Request $request, $lineId, $processId, $date)
    {
        // Get all hourly data for this line, process, and date
        $productionHourly = ProductionHourly::with(['line', 'process'])
            ->where('line_id', $lineId)
            ->where('process_id', $processId)
            ->whereDate('production_date', $date)
            ->whereNotNull('total_production')
            ->where('total_production', '!=', '')
            ->orderBy('hour', 'asc')
            ->get();

        if ($productionHourly->isEmpty()) {
            return redirect()->route('production-hourly.index')->with('error', 'Data tidak ditemukan.');
        }

        // Get daily grades
        $dailyGrade = ProductionDailyGrade::where('line_id', $lineId)
            ->where('process_id', $processId)
            ->whereDate('production_date', $date)
            ->first();

        // Calculate totals
        $totalProduction = 0;
        $totalTarget = 0;
        foreach ($productionHourly as $item) {
            $production = $item->total_production;
            if ($production !== '(istirahat)' && is_numeric($production)) {
                $totalProduction += (int)$production;
            }
            if ($item->target_per_hour) {
                $totalTarget += $item->target_per_hour;
            }
        }

        $gradeB = $dailyGrade->grade_b ?? 0;
        $gradeC = $dailyGrade->grade_c ?? 0;
        // Yang diinput per jam adalah Grade A, jadi totalProduction = Grade A
        $gradeA = $totalProduction;
        // Total Produksi = Grade A + Grade B + Grade C
        $totalProduction = $gradeA + $gradeB + $gradeC;

        return view('production_hourly.show', compact('productionHourly', 'dailyGrade', 'totalProduction', 'totalTarget', 'gradeA', 'gradeB', 'gradeC', 'date'));
    }

    /**
     * Display the specified resource by ID (for resource route compatibility).
     */
    public function showById(string $id)
    {
        $productionHourly = ProductionHourly::with(['line', 'process'])->findOrFail($id);
        return redirect()->route('production-hourly.show-detail', [
            $productionHourly->line_id,
            $productionHourly->process_id,
            $productionHourly->production_date->format('Y-m-d')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $productionHourly = ProductionHourly::findOrFail($id);
        
        $lines = Line::with('process')
            ->whereNotNull('process_id')
            ->orderBy('name', 'asc')
            ->get();
        
        $processes = Process::orderBy('name', 'asc')->get();

        return view('production_hourly.edit', compact('productionHourly', 'lines', 'processes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productionHourly = ProductionHourly::findOrFail($id);

        $validated = $request->validate([
            'line_id' => 'required|exists:lines,id',
            'process_id' => 'required|exists:processes,id',
            'production_date' => 'required|date',
            'hour' => 'required|integer|min:0|max:23',
            'total_production' => 'required|integer|min:0',
            'grade_b' => 'required|integer|min:0',
            'grade_c' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if record already exists for this line, process, date, and hour (excluding current record)
        $existing = ProductionHourly::where('line_id', $validated['line_id'])
            ->where('process_id', $validated['process_id'])
            ->whereDate('production_date', $validated['production_date'])
            ->where('hour', $validated['hour'])
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return back()->withErrors(['hour' => 'Data produksi untuk Line, Process, Tanggal, dan Jam ini sudah ada.'])->withInput();
        }

        // Validate that grade_b + grade_c doesn't exceed total_production
        if ($validated['grade_b'] + $validated['grade_c'] > $validated['total_production']) {
            return back()->withErrors(['grade_b' => 'Jumlah Grade B + Grade C tidak boleh melebihi Total Produksi.'])->withInput();
        }

        $productionHourly->update($validated);

        return redirect()->route('production-hourly.index')->with('success', 'Data produksi per jam berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productionHourly = ProductionHourly::findOrFail($id);
        $productionHourly->delete();

        return redirect()->route('production-hourly.index')->with('success', 'Data produksi per jam berhasil dihapus.');
    }

    /**
     * Bulk fill target per hour for all hours (0-23) for a specific date and line
     */
    public function bulkFillTarget(Request $request)
    {
        $validated = $request->validate([
            'line_id' => 'required|exists:lines,id',
            'process_id' => 'required|exists:processes,id',
            'production_date' => 'required|date',
            'target_per_hour' => 'required|integer|min:0',
        ]);

        $updated = 0;
        $created = 0;

        // Update or create target for all hours (0-23)
        for ($hour = 0; $hour < 24; $hour++) {
            $productionHourly = ProductionHourly::updateOrCreate(
                [
                    'line_id' => $validated['line_id'],
                    'process_id' => $validated['process_id'],
                    'production_date' => $validated['production_date'],
                    'hour' => $hour,
                ],
                [
                    'target_per_hour' => $validated['target_per_hour'],
                ]
            );

            if ($productionHourly->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengisi target untuk semua jam. {$created} record baru dibuat, {$updated} record diupdate.",
            'created' => $created,
            'updated' => $updated,
        ]);
    }
}
