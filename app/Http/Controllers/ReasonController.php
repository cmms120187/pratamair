<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Reason;
use App\Models\System;
use App\Models\Problem;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;

class ReasonController extends Controller
{
    public function index(Request $request)
    {
        $query = Reason::query();
        
        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        
        // Validate sort_by and sort_dir
        $allowedSorts = ['id', 'name', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortDir);
        
        $reasons = $query->paginate(12)->withQueryString();
        
        return view('reasons.index', compact('reasons', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        // Get all systems for dropdown
        $systemsQuery = System::orderBy('nama_sistem', 'asc')->get();
        
        // Get all problems with their systems for client-side filtering
        $problemsQuery = Problem::with('systems')->orderBy('problem_header', 'asc')->orderBy('name', 'asc')->get();
        
        // Map systems data for JavaScript
        $systems = [];
        foreach ($systemsQuery as $system) {
            $systems[] = [
                'id' => (string)$system->id,
                'nama_sistem' => $system->nama_sistem ?? '',
            ];
        }
        
        // Map problems data with their system IDs for JavaScript
        $problems = [];
        foreach ($problemsQuery as $problem) {
            $systemIds = $problem->systems->pluck('id')->map(function($id) {
                return (string)$id;
            })->toArray();
            
            $problems[] = [
                'id' => (string)$problem->id,
                'name' => $problem->name ?? '',
                'problem_header' => $problem->problem_header ?? '',
                'problem_mm' => $problem->problem_mm ?? '',
                'system_ids' => $systemIds,
            ];
        }
        
        return view('reasons.create', compact('systems', 'problems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'system_select' => 'required|exists:systems,id',
            'problem_select' => 'required|exists:problems,id',
        ]);
        
        $reason = new Reason();
        $reason->name = $validated['name'];
        $reason->system_id = $validated['system_select'];
        $reason->problem_id = $validated['problem_select'];
        $reason->save();
        return redirect()->route('reasons.index')->with('success', 'Reason created successfully.');
    }

    public function edit($id)
    {
        $reason = Reason::findOrFail($id);
        
        // Get all systems for dropdown
        $systemsQuery = System::orderBy('nama_sistem', 'asc')->get();
        
        // Get all problems with their systems for client-side filtering
        $problemsQuery = Problem::with('systems')->orderBy('problem_header', 'asc')->orderBy('name', 'asc')->get();
        
        // Map systems data for JavaScript
        $systems = [];
        foreach ($systemsQuery as $system) {
            $systems[] = [
                'id' => (string)$system->id,
                'nama_sistem' => $system->nama_sistem ?? '',
            ];
        }
        
        // Map problems data with their system IDs for JavaScript
        $problems = [];
        foreach ($problemsQuery as $problem) {
            $systemIds = $problem->systems->pluck('id')->map(function($id) {
                return (string)$id;
            })->toArray();
            
            $problems[] = [
                'id' => (string)$problem->id,
                'name' => $problem->name ?? '',
                'problem_header' => $problem->problem_header ?? '',
                'problem_mm' => $problem->problem_mm ?? '',
                'system_ids' => $systemIds,
            ];
        }
        
        // Get current system_id and problem_id from reason
        $currentSystemId = $reason->system_id ? (string)$reason->system_id : null;
        $currentProblemId = $reason->problem_id ? (string)$reason->problem_id : null;
        
        return view('reasons.edit', compact('reason', 'systems', 'problems', 'currentSystemId', 'currentProblemId'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // system_select and problem_select are locked in edit, so we don't validate them
        ]);
        
        $reason = Reason::findOrFail($id);
        $reason->name = $validated['name'];
        // system_id and problem_id remain unchanged (locked in edit form)
        $reason->save();
        return redirect()->route('reasons.index')->with('success', 'Reason updated successfully.');
    }

    public function destroy($id)
    {
        $reason = Reason::findOrFail($id);
        $reason->delete();
        return redirect()->route('reasons.index')->with('success', 'Reason deleted successfully.');
    }

    /**
     * Preview unique reasons from downtime erp or downtime erp2
     */
    public function previewFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin can access this feature.',
            ], 403);
        }

        $request->validate([
            'data_source' => 'required|in:downtime_erp,downtime_erp2',
        ]);

        $dataSource = $request->data_source;

        try {
            if ($dataSource === 'downtime_erp') {
                $uniqueCombinations = DowntimeErp::whereNotNull('problemDowntime')
                    ->whereNotNull('reasonDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'];
                    })
                    ->values();
            } else {
                $uniqueCombinations = DowntimeErp2::whereNotNull('problemDowntime')
                    ->whereNotNull('reasonDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime', 'system_id')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                            'system_id' => $item->system_id,
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'];
                    })
                    ->values();
            }

            // Check existing
            $existingCount = 0;
            $newCount = 0;
            foreach ($uniqueCombinations as $combo) {
                $problem = Problem::where('name', $combo['problem'])->first();
                if ($problem) {
                    $existing = Reason::where('name', $combo['reason'])
                        ->where('problem_id', $problem->id)
                        ->first();
                    if ($existing) {
                        $existingCount++;
                    } else {
                        $newCount++;
                    }
                } else {
                    $newCount++;
                }
            }

            return response()->json([
                'success' => true,
                'data_source' => $dataSource,
                'total_unique' => $uniqueCombinations->count(),
                'existing_count' => $existingCount,
                'new_count' => $newCount,
                'sample_data' => $uniqueCombinations->take(20)->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract unique reasons from downtime erp or downtime erp2 with problem relations
     */
    public function extractFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('reasons.index')
                ->with('error', 'Unauthorized. Only admin can access this feature.');
        }

        $request->validate([
            'data_source' => 'required|in:downtime_erp,downtime_erp2',
        ]);

        $dataSource = $request->data_source;
        $created = 0;
        $skipped = 0;
        $errors = [];

        try {
            if ($dataSource === 'downtime_erp') {
                // Get unique combinations of problem and reason from downtime_erp
                $uniqueCombinations = DowntimeErp::whereNotNull('problemDowntime')
                    ->whereNotNull('reasonDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'];
                    })
                    ->values();
            } else {
                // Get unique combinations of problem and reason from downtime_erp2
                $uniqueCombinations = DowntimeErp2::whereNotNull('problemDowntime')
                    ->whereNotNull('reasonDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime', 'system_id')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                            'system_id' => $item->system_id,
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'];
                    })
                    ->values();
            }

            DB::beginTransaction();

            foreach ($uniqueCombinations as $combination) {
                $problemName = $combination['problem'];
                $reasonName = $combination['reason'];
                $systemId = $combination['system_id'] ?? null;

                // Find or create problem
                $problem = Problem::where('name', $problemName)->first();
                if (!$problem) {
                    // Create problem if not exists
                    try {
                        $problem = Problem::create(['name' => $problemName]);
                    } catch (\Exception $e) {
                        $errors[] = "Failed to create problem '{$problemName}': " . $e->getMessage();
                        continue;
                    }
                }

                // Check if reason already exists with this problem
                $existingReason = Reason::where('name', $reasonName)
                    ->where('problem_id', $problem->id)
                    ->first();

                if ($existingReason) {
                    // Update system_id if provided and different
                    if ($systemId && $existingReason->system_id != $systemId) {
                        $existingReason->system_id = $systemId;
                        $existingReason->save();
                    }
                    $skipped++;
                    continue;
                }

                // Create new reason
                try {
                    Reason::create([
                        'name' => $reasonName,
                        'problem_id' => $problem->id,
                        'system_id' => $systemId,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create reason '{$reasonName}' for problem '{$problemName}': " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Extraction completed! Created: {$created}, Skipped: {$skipped}";
            if (!empty($errors)) {
                $message .= ". Errors: " . count($errors);
            }

            return redirect()->route('reasons.index')
                ->with('success', $message)
                ->with('extraction_details', [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('reasons.index')
                ->with('error', 'Extraction failed: ' . $e->getMessage());
        }
    }
}
