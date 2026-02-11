<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Action;
use App\Models\System;
use App\Models\Problem;
use App\Models\Reason;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;

class ActionController extends Controller
{
    public function index(Request $request)
    {
        $query = Action::with(['system', 'problem', 'reason']);
        
        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        
        // Validate sort_by and sort_dir
        $allowedSorts = ['name', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }
        
        // Handle sorting for related models using subqueries to avoid duplicates
        if ($sortBy === 'system') {
            $query->orderByRaw("(SELECT nama_sistem FROM systems WHERE systems.id = actions.system_id) {$sortDir}");
        } elseif ($sortBy === 'problem') {
            $query->orderByRaw("(SELECT name FROM problems WHERE problems.id = actions.problem_id) {$sortDir}");
        } elseif ($sortBy === 'reason') {
            $query->orderByRaw("(SELECT name FROM reasons WHERE reasons.id = actions.reason_id) {$sortDir}");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        
        $actions = $query->paginate(12)->withQueryString();
        
        return view('actions.index', compact('actions', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        // Get all systems for dropdown
        $systemsQuery = System::orderBy('nama_sistem', 'asc')->get();
        
        // Get all problems with their systems for client-side filtering
        $problemsQuery = Problem::with('systems')->orderBy('problem_header', 'asc')->orderBy('name', 'asc')->get();
        
        // Get all reasons for dropdown (will be filtered by problem selection)
        $reasonsQuery = Reason::orderBy('name', 'asc')->get();
        
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
        
        // Map reasons data for JavaScript (include problem_id for filtering)
        $reasons = [];
        foreach ($reasonsQuery as $reason) {
            $reasons[] = [
                'id' => (string)$reason->id,
                'name' => $reason->name ?? '',
                'problem_id' => $reason->problem_id ? (string)$reason->problem_id : null,
            ];
        }
        
        return view('actions.create', compact('systems', 'problems', 'reasons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'system_select' => 'required|exists:systems,id',
            'problem_select' => 'required|exists:problems,id',
            'reason_select' => 'required|exists:reasons,id',
        ]);
        
        $action = new Action();
        $action->name = $validated['name'];
        $action->system_id = $validated['system_select'];
        $action->problem_id = $validated['problem_select'];
        $action->reason_id = $validated['reason_select'];
        $action->save();
        return redirect()->route('actions.index')->with('success', 'Action created successfully.');
    }

    public function edit($id)
    {
        $action = Action::findOrFail($id);
        
        // Get all systems for dropdown
        $systemsQuery = System::orderBy('nama_sistem', 'asc')->get();
        
        // Get all problems with their systems for client-side filtering
        $problemsQuery = Problem::with('systems')->orderBy('problem_header', 'asc')->orderBy('name', 'asc')->get();
        
        // Get all reasons for dropdown (will be filtered by problem selection)
        $reasonsQuery = Reason::orderBy('name', 'asc')->get();
        
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
        
        // Map reasons data for JavaScript (include problem_id for filtering)
        $reasons = [];
        foreach ($reasonsQuery as $reason) {
            $reasons[] = [
                'id' => (string)$reason->id,
                'name' => $reason->name ?? '',
                'problem_id' => $reason->problem_id ? (string)$reason->problem_id : null,
            ];
        }
        
        // Get current system_id, problem_id, and reason_id from action
        $currentSystemId = $action->system_id ? (string)$action->system_id : null;
        $currentProblemId = $action->problem_id ? (string)$action->problem_id : null;
        $currentReasonId = $action->reason_id ? (string)$action->reason_id : null;
        
        return view('actions.edit', compact('action', 'systems', 'problems', 'reasons', 'currentSystemId', 'currentProblemId', 'currentReasonId'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // system_select, problem_select, and reason_select are locked in edit, so we don't validate them
        ]);
        
        $action = Action::findOrFail($id);
        $action->name = $validated['name'];
        // system_id, problem_id, and reason_id remain unchanged (locked in edit form)
        $action->save();
        return redirect()->route('actions.index')->with('success', 'Action updated successfully.');
    }

    public function destroy($id)
    {
        $action = Action::findOrFail($id);
        $action->delete();
        return redirect()->route('actions.index')->with('success', 'Action deleted successfully.');
    }

    /**
     * Preview unique actions from downtime erp or downtime erp2
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
                    ->whereNotNull('actionDowtime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->where('actionDowtime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime', 'actionDowtime')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                            'action' => trim($item->actionDowtime),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']) && !empty($item['action']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'] . '|' . $item['action'];
                    })
                    ->values();
            } else {
                $uniqueCombinations = DowntimeErp2::whereNotNull('problemDowntime')
                    ->whereNotNull('reasonDowntime')
                    ->whereNotNull('actionDowtime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->where('actionDowtime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime', 'actionDowtime', 'system_id')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                            'action' => trim($item->actionDowtime),
                            'system_id' => $item->system_id,
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']) && !empty($item['action']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'] . '|' . $item['action'];
                    })
                    ->values();
            }

            // Check existing
            $existingCount = 0;
            $newCount = 0;
            foreach ($uniqueCombinations as $combo) {
                $problem = Problem::where('name', $combo['problem'])->first();
                if ($problem) {
                    $reason = Reason::where('name', $combo['reason'])
                        ->where('problem_id', $problem->id)
                        ->first();
                    if ($reason) {
                        $existing = Action::where('name', $combo['action'])
                            ->where('problem_id', $problem->id)
                            ->where('reason_id', $reason->id)
                            ->first();
                        if ($existing) {
                            $existingCount++;
                        } else {
                            $newCount++;
                        }
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
     * Extract unique actions from downtime erp or downtime erp2 with problem and reason relations
     */
    public function extractFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('actions.index')
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
                // Get unique combinations of problem, reason, and action from downtime_erp
                $uniqueCombinations = DowntimeErp::whereNotNull('problemDowntime')
                    ->whereNotNull('reasonDowntime')
                    ->whereNotNull('actionDowtime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->where('actionDowtime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime', 'actionDowtime')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                            'action' => trim($item->actionDowtime),
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']) && !empty($item['action']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'] . '|' . $item['action'];
                    })
                    ->values();
            } else {
                // Get unique combinations of problem, reason, and action from downtime_erp2
                $uniqueCombinations = DowntimeErp2::whereNotNull('problemDowntime')
                    ->whereNotNull('reasonDowntime')
                    ->whereNotNull('actionDowtime')
                    ->where('problemDowntime', '!=', '')
                    ->where('reasonDowntime', '!=', '')
                    ->where('actionDowtime', '!=', '')
                    ->select('problemDowntime', 'reasonDowntime', 'actionDowtime', 'system_id')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return [
                            'problem' => trim($item->problemDowntime),
                            'reason' => trim($item->reasonDowntime),
                            'action' => trim($item->actionDowtime),
                            'system_id' => $item->system_id,
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['problem']) && !empty($item['reason']) && !empty($item['action']);
                    })
                    ->unique(function($item) {
                        return $item['problem'] . '|' . $item['reason'] . '|' . $item['action'];
                    })
                    ->values();
            }

            DB::beginTransaction();

            foreach ($uniqueCombinations as $combination) {
                $problemName = $combination['problem'];
                $reasonName = $combination['reason'];
                $actionName = $combination['action'];
                $systemId = $combination['system_id'] ?? null;

                // Find or create problem
                $problem = Problem::where('name', $problemName)->first();
                if (!$problem) {
                    try {
                        $problem = Problem::create(['name' => $problemName]);
                    } catch (\Exception $e) {
                        $errors[] = "Failed to create problem '{$problemName}': " . $e->getMessage();
                        continue;
                    }
                }

                // Find or create reason
                $reason = Reason::where('name', $reasonName)
                    ->where('problem_id', $problem->id)
                    ->first();
                if (!$reason) {
                    try {
                        $reason = Reason::create([
                            'name' => $reasonName,
                            'problem_id' => $problem->id,
                            'system_id' => $systemId,
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = "Failed to create reason '{$reasonName}' for problem '{$problemName}': " . $e->getMessage();
                        continue;
                    }
                }

                // Check if action already exists with this problem and reason
                $existingAction = Action::where('name', $actionName)
                    ->where('problem_id', $problem->id)
                    ->where('reason_id', $reason->id)
                    ->first();

                if ($existingAction) {
                    // Update system_id if provided and different
                    if ($systemId && $existingAction->system_id != $systemId) {
                        $existingAction->system_id = $systemId;
                        $existingAction->save();
                    }
                    $skipped++;
                    continue;
                }

                // Create new action
                try {
                    Action::create([
                        'name' => $actionName,
                        'problem_id' => $problem->id,
                        'reason_id' => $reason->id,
                        'system_id' => $systemId,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create action '{$actionName}' for problem '{$problemName}' and reason '{$reasonName}': " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Extraction completed! Created: {$created}, Skipped: {$skipped}";
            if (!empty($errors)) {
                $message .= ". Errors: " . count($errors);
            }

            return redirect()->route('actions.index')
                ->with('success', $message)
                ->with('extraction_details', [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('actions.index')
                ->with('error', 'Extraction failed: ' . $e->getMessage());
        }
    }
}
