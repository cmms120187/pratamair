<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionDailyGrade;
use App\Models\ProductionDailyDowntime;
use App\Models\Line;
use App\Models\Process;
use App\Models\Plant;
use App\Models\RoomErp;

class ProductionDailyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductionDailyGrade::with(['line', 'process']);

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

        // Get all data - sorted by date ascending, then line ascending
        $productionDaily = $query->orderBy('production_date', 'asc')
            ->orderBy('line_id', 'asc')
            ->paginate(50);

        // Calculate total production (Grade A + Grade B + Grade C) for each record
        foreach ($productionDaily as $item) {
            // Get Grade A from ProductionHourly where hour = 0 (daily total)
            // If not found, try to get from sum of all hours
            $gradeA = \App\Models\ProductionHourly::where('line_id', $item->line_id)
                ->where('process_id', $item->process_id)
                ->whereDate('production_date', $item->production_date)
                ->where('hour', 0)
                ->whereNotNull('total_production')
                ->where('total_production', '!=', '')
                ->value('total_production');
            
            // If not found with hour = 0, try sum of all hours
            if (!$gradeA) {
                $gradeA = \App\Models\ProductionHourly::where('line_id', $item->line_id)
                    ->where('process_id', $item->process_id)
                    ->whereDate('production_date', $item->production_date)
                    ->whereNotNull('total_production')
                    ->where('total_production', '!=', '')
                    ->sum('total_production');
            }
            
            $item->grade_a = (int) $gradeA;
            $item->total_production = $item->grade_a + $item->grade_b + $item->grade_c;
            
            // Get target_per_hour from ProductionHourly or ProductionDailyGrade
            $targetPerHour = \App\Models\ProductionHourly::where('line_id', $item->line_id)
                ->where('process_id', $item->process_id)
                ->whereDate('production_date', $item->production_date)
                ->where('hour', 0)
                ->value('target_per_hour');
            
            if ($targetPerHour === null) {
                $targetPerHour = $item->target_per_hour;
            }
            
            $item->target_per_hour = $targetPerHour ?? 0;
            
            // Calculate production hours (end_time - start_time - break_duration)
            if ($item->start_time && $item->end_time) {
                // Parse time strings and calculate difference
                $startParts = explode(':', $item->start_time);
                $endParts = explode(':', $item->end_time);
                
                $startMinutes = (int)$startParts[0] * 60 + (int)($startParts[1] ?? 0);
                $endMinutes = (int)$endParts[0] * 60 + (int)($endParts[1] ?? 0);
                
                // Handle case where end_time is next day (e.g., 23:00 to 01:00)
                if ($endMinutes < $startMinutes) {
                    $endMinutes += 24 * 60; // Add 24 hours
                }
                
                $totalMinutes = $endMinutes - $startMinutes;
                $totalHours = $totalMinutes / 60;
                $breakDuration = $item->break_duration ?? 0;
                $item->production_hours = max(0, $totalHours - $breakDuration);
            } else {
                $item->production_hours = 0;
            }
            
            // Calculate target per day (target_per_hour * production_hours)
            $item->target_per_day = $item->target_per_hour * $item->production_hours;
        }

        // Get lines and processes for filters
        $lines = Line::with('process')->orderBy('name', 'asc')->get();
        $processes = Process::orderBy('name', 'asc')->get();

        return view('production_daily.index', compact('productionDaily', 'lines', 'processes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get RoomErp dengan category Production only
        $roomErps = RoomErp::where('category', 'Production')
            ->orderBy('plant_name', 'asc')
            ->orderBy('process_name', 'asc')
            ->orderBy('line_name', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Get unique plants, processes, lines from RoomErp
        $plants = $roomErps->pluck('plant_name')->filter()->unique()->sort()->values();
        $processes = $roomErps->pluck('process_name')->filter()->unique()->sort()->values();
        $lines = $roomErps->pluck('line_name')->filter()->unique()->sort()->values();
        $rooms = $roomErps;

        return view('production_daily.create', compact('plants', 'processes', 'lines', 'rooms', 'roomErps'));
    }

    /**
     * Get processes by plant from RoomErp
     */
    public function getProcessesByPlant(Request $request)
    {
        $plantName = $request->get('plant_name');
        
        if (!$plantName) {
            return response()->json([]);
        }

        $processes = RoomErp::where('category', 'Production')
            ->where('plant_name', $plantName)
            ->distinct()
            ->pluck('process_name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(function($process) {
                return ['name' => $process];
            });

        return response()->json($processes);
    }

    /**
     * Get lines by plant and process from RoomErp
     */
    public function getLinesByPlantAndProcess(Request $request)
    {
        $plantName = $request->get('plant_name');
        $processName = $request->get('process_name');
        
        if (!$plantName || !$processName) {
            return response()->json([]);
        }

        $lines = RoomErp::where('category', 'Production')
            ->where('plant_name', $plantName)
            ->where('process_name', $processName)
            ->distinct()
            ->pluck('line_name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(function($line) {
                return ['name' => $line];
            });

        return response()->json($lines);
    }

    /**
     * Get rooms by plant, process, and line from RoomErp
     */
    public function getRoomsByPlantProcessAndLine(Request $request)
    {
        $plantName = $request->get('plant_name');
        $processName = $request->get('process_name');
        $lineName = $request->get('line_name');
        
        if (!$plantName || !$processName || !$lineName) {
            return response()->json([]);
        }

        $rooms = RoomErp::where('category', 'Production')
            ->where('plant_name', $plantName)
            ->where('process_name', $processName)
            ->where('line_name', $lineName)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($room) {
                return [
                    'id' => $room->id,
                    'kode_room' => $room->kode_room,
                    'name' => $room->name,
                ];
            });

        return response()->json($rooms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_erp_id' => 'required|exists:room_erp,id',
            'production_date' => 'required|date',
            'target_per_hour' => 'required|integer|min:0',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grade_a' => 'required|integer|min:0',
            'grade_b' => 'nullable|integer|min:0',
            'grade_c' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Get RoomErp data
        $roomErp = RoomErp::findOrFail($validated['room_erp_id']);
        
        // Find or create Plant, Process, Line based on RoomErp data
        $plant = Plant::firstOrCreate(['name' => $roomErp->plant_name]);
        $process = Process::firstOrCreate(['name' => $roomErp->process_name]);
        $line = Line::firstOrCreate(
            [
                'name' => $roomErp->line_name,
                'plant_id' => $plant->id,
                'process_id' => $process->id,
            ]
        );

        // Check if record already exists for this line, process, and date
        $existing = ProductionDailyGrade::where('line_id', $line->id)
            ->where('process_id', $process->id)
            ->whereDate('production_date', $validated['production_date'])
            ->first();

        if ($existing) {
            return back()->withErrors(['production_date' => 'Data produksi untuk Room, Process, dan Tanggal ini sudah ada. Silakan edit data yang sudah ada.'])->withInput();
        }

        // Calculate break duration based on day of week
        $productionDate = \Carbon\Carbon::parse($validated['production_date']);
        $dayOfWeek = $productionDate->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 5 = Friday, 6 = Saturday
        $breakDuration = ($dayOfWeek == 5) ? 1.5 : 1.0; // Friday = 1.5, Monday-Thursday = 1.0

        // Store daily grade (grade_b and grade_c)
        $productionDailyGrade = ProductionDailyGrade::create([
            'line_id' => $line->id,
            'process_id' => $process->id,
            'production_date' => $validated['production_date'],
            'target_per_hour' => $validated['target_per_hour'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'break_duration' => $breakDuration,
            'grade_b' => $validated['grade_b'] ?? 0,
            'grade_c' => $validated['grade_c'] ?? 0,
        ]);

        // Store grade_a as ProductionHourly record with hour = 0 to represent daily total
        \App\Models\ProductionHourly::updateOrCreate(
            [
                'line_id' => $line->id,
                'process_id' => $process->id,
                'production_date' => $validated['production_date'],
                'hour' => 0, // Use hour 0 to represent daily total
            ],
            [
                'target_per_hour' => $validated['target_per_hour'],
                'total_production' => $validated['grade_a'],
            ]
        );

        // Store production downtimes if provided
        if (isset($validated['downtimes']) && is_array($validated['downtimes'])) {
            foreach ($validated['downtimes'] as $downtimeData) {
                \App\Models\ProductionDailyDowntime::create([
                    'production_daily_grade_id' => $productionDailyGrade->id,
                    'downtime_type' => $downtimeData['downtime_type'],
                    'start_time' => $downtimeData['start_time'],
                    'end_time' => $downtimeData['end_time'],
                    'duration_minutes' => $downtimeData['duration_minutes'],
                    'description' => $downtimeData['description'] ?? null,
                    'include_oee' => isset($downtimeData['include_oee']) && $downtimeData['include_oee'] == '1',
                ]);
            }
        }

        return redirect()->route('production-daily.index')->with('success', 'Data produksi per hari berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $productionDaily = ProductionDailyGrade::with(['line', 'process'])->findOrFail($id);

        // Get Grade A from ProductionHourly
        $gradeA = \App\Models\ProductionHourly::where('line_id', $productionDaily->line_id)
            ->where('process_id', $productionDaily->process_id)
            ->whereDate('production_date', $productionDaily->production_date)
            ->where('hour', 0)
            ->whereNotNull('total_production')
            ->where('total_production', '!=', '')
            ->value('total_production');
        
        // If not found with hour = 0, try sum of all hours
        if (!$gradeA) {
            $gradeA = \App\Models\ProductionHourly::where('line_id', $productionDaily->line_id)
                ->where('process_id', $productionDaily->process_id)
                ->whereDate('production_date', $productionDaily->production_date)
                ->whereNotNull('total_production')
                ->where('total_production', '!=', '')
                ->sum('total_production');
        }

        $productionDaily->grade_a = (int) $gradeA;
        $productionDaily->total_production = $productionDaily->grade_a + $productionDaily->grade_b + $productionDaily->grade_c;

        return redirect()->route('production-daily.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $productionDaily = ProductionDailyGrade::with(['line.plant', 'process'])->findOrFail($id);

        // Get Grade A from ProductionHourly where hour = 0 (daily total)
        $gradeA = \App\Models\ProductionHourly::where('line_id', $productionDaily->line_id)
            ->where('process_id', $productionDaily->process_id)
            ->whereDate('production_date', $productionDaily->production_date)
            ->where('hour', 0)
            ->whereNotNull('total_production')
            ->where('total_production', '!=', '')
            ->value('total_production');
        
        // If not found with hour = 0, try sum of all hours
        if (!$gradeA) {
            $gradeA = \App\Models\ProductionHourly::where('line_id', $productionDaily->line_id)
                ->where('process_id', $productionDaily->process_id)
                ->whereDate('production_date', $productionDaily->production_date)
                ->whereNotNull('total_production')
                ->where('total_production', '!=', '')
                ->sum('total_production');
        }

        $productionDaily->grade_a = (int) $gradeA;

        // Get target_per_hour from ProductionHourly where hour = 0 (daily total)
        $targetPerHour = \App\Models\ProductionHourly::where('line_id', $productionDaily->line_id)
            ->where('process_id', $productionDaily->process_id)
            ->whereDate('production_date', $productionDaily->production_date)
            ->where('hour', 0)
            ->value('target_per_hour');
        
        // If target_per_hour is not in ProductionHourly, use from ProductionDailyGrade
        if ($targetPerHour === null) {
            $targetPerHour = $productionDaily->target_per_hour;
        }
        
        $productionDaily->target_per_hour = $targetPerHour;

        // Get RoomErp data based on line and process
        $roomErp = null;
        if ($productionDaily->line && $productionDaily->process) {
            $roomErp = RoomErp::where('category', 'Production')
                ->where('plant_name', $productionDaily->line->plant->name ?? '')
                ->where('process_name', $productionDaily->process->name ?? '')
                ->where('line_name', $productionDaily->line->name ?? '')
                ->first();
        }

        // Get RoomErp dengan category Production only for dropdowns
        $roomErps = RoomErp::where('category', 'Production')
            ->orderBy('plant_name', 'asc')
            ->orderBy('process_name', 'asc')
            ->orderBy('line_name', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Get unique plants, processes, lines from RoomErp
        $plants = $roomErps->pluck('plant_name')->filter()->unique()->sort()->values();
        $processes = $roomErps->pluck('process_name')->filter()->unique()->sort()->values();
        $lines = $roomErps->pluck('line_name')->filter()->unique()->sort()->values();
        $rooms = $roomErps;

        return view('production_daily.edit', compact('productionDaily', 'plants', 'processes', 'lines', 'rooms', 'roomErps', 'roomErp'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $productionDaily = ProductionDailyGrade::findOrFail($id);

        $validated = $request->validate([
            'room_erp_id' => 'required|exists:room_erp,id',
            'production_date' => 'required|date',
            'target_per_hour' => 'required|integer|min:0',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grade_a' => 'required|integer|min:0',
            'grade_b' => 'nullable|integer|min:0',
            'grade_c' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Get RoomErp data
        $roomErp = RoomErp::findOrFail($validated['room_erp_id']);
        
        // Find or create Plant, Process, Line based on RoomErp data
        $plant = Plant::firstOrCreate(['name' => $roomErp->plant_name]);
        $process = Process::firstOrCreate(['name' => $roomErp->process_name]);
        $line = Line::firstOrCreate(
            [
                'name' => $roomErp->line_name,
                'plant_id' => $plant->id,
                'process_id' => $process->id,
            ]
        );

        // Check if another record exists with same line, process, and date (excluding current)
        $existing = ProductionDailyGrade::where('line_id', $line->id)
            ->where('process_id', $process->id)
            ->whereDate('production_date', $validated['production_date'])
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return back()->withErrors(['production_date' => 'Data produksi untuk Room, Process, dan Tanggal ini sudah ada.'])->withInput();
        }

        // Calculate break duration based on day of week
        $productionDate = \Carbon\Carbon::parse($validated['production_date']);
        $dayOfWeek = $productionDate->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 5 = Friday, 6 = Saturday
        $breakDuration = ($dayOfWeek == 5) ? 1.5 : 1.0; // Friday = 1.5, Monday-Thursday = 1.0

        // Update daily grade
        $productionDaily->update([
            'line_id' => $line->id,
            'process_id' => $process->id,
            'production_date' => $validated['production_date'],
            'target_per_hour' => $validated['target_per_hour'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'break_duration' => $breakDuration,
            'grade_b' => $validated['grade_b'] ?? 0,
            'grade_c' => $validated['grade_c'] ?? 0,
        ]);

        // Update or create Grade A in ProductionHourly
        \App\Models\ProductionHourly::updateOrCreate(
            [
                'line_id' => $line->id,
                'process_id' => $process->id,
                'production_date' => $validated['production_date'],
                'hour' => 0,
            ],
            [
                'target_per_hour' => $validated['target_per_hour'],
                'total_production' => $validated['grade_a'],
            ]
        );

        // Delete existing downtimes and recreate
        \App\Models\ProductionDailyDowntime::where('production_daily_grade_id', $productionDaily->id)->delete();

        // Store production downtimes if provided
        if (isset($validated['downtimes']) && is_array($validated['downtimes'])) {
            foreach ($validated['downtimes'] as $downtimeData) {
                \App\Models\ProductionDailyDowntime::create([
                    'production_daily_grade_id' => $productionDaily->id,
                    'downtime_type' => $downtimeData['downtime_type'],
                    'start_time' => $downtimeData['start_time'],
                    'end_time' => $downtimeData['end_time'],
                    'duration_minutes' => $downtimeData['duration_minutes'],
                    'description' => $downtimeData['description'] ?? null,
                    'include_oee' => isset($downtimeData['include_oee']) && $downtimeData['include_oee'] == '1',
                ]);
            }
        }

        return redirect()->route('production-daily.index')->with('success', 'Data produksi per hari berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productionDaily = ProductionDailyGrade::findOrFail($id);

        // Also delete related ProductionHourly records for this day
        \App\Models\ProductionHourly::where('line_id', $productionDaily->line_id)
            ->where('process_id', $productionDaily->process_id)
            ->whereDate('production_date', $productionDaily->production_date)
            ->where('hour', 0)
            ->delete();

        $productionDaily->delete();

        return redirect()->route('production-daily.index')->with('success', 'Data produksi per hari berhasil dihapus.');
    }
}

