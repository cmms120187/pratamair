<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Problem;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        $query = Problem::with('systems');
        
        // Filter by system
        if ($request->filled('filter_system')) {
            $query->whereHas('systems', function($q) use ($request) {
                $q->where('systems.id', $request->filter_system);
            });
        }
        
        // Filter by problem_header
        if ($request->filled('filter_problem_header')) {
            $query->where('problem_header', $request->filter_problem_header);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'problem_header');
        $sortDir = $request->get('sort_dir', 'asc');
        
        // Validate sort_by and sort_dir
        $allowedSorts = ['id', 'name', 'problem_header', 'problem_mm', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'problem_header';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }
        
        // Apply sorting
        if ($sortBy === 'problem_header') {
            $query->orderBy('problem_header', $sortDir)->orderBy('name', 'asc');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        
        $problems = $query->paginate(12)->withQueryString();
        
        // Get filter options
        $systems = \App\Models\System::orderBy('nama_sistem')->get();
        $problemHeaders = Problem::whereNotNull('problem_header')->distinct()->orderBy('problem_header')->pluck('problem_header');
        
        return view('problems.index', compact('problems', 'systems', 'problemHeaders', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        $systems = \App\Models\System::orderBy('nama_sistem')->get();
        return view('problems.create', compact('systems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:problems,name',
            'problem_header' => 'nullable|string|max:255',
            'problem_mm' => 'nullable|string|max:255',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ]);
        
        $problem = Problem::create($validated);
        
        if ($request->filled('systems')) {
            $problem->systems()->sync($request->input('systems', []));
        }
        
        return redirect()->route('problems.index')->with('success', 'Problem created successfully.');
    }

    public function edit($id)
    {
        $problem = Problem::with('systems')->findOrFail($id);
        $systems = \App\Models\System::orderBy('nama_sistem')->get();
        return view('problems.edit', compact('problem', 'systems'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:problems,name,' . $id,
            'problem_header' => 'nullable|string|max:255',
            'problem_mm' => 'nullable|string|max:255',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ]);
        
        $problem = Problem::findOrFail($id);
        $problem->update($validated);
        
        if ($request->has('systems')) {
            $problem->systems()->sync($request->input('systems', []));
        }
        
        return redirect()->route('problems.index')->with('success', 'Problem updated successfully.');
    }

    public function destroy($id)
    {
        $problem = Problem::findOrFail($id);
        $problem->delete();
        return redirect()->route('problems.index')->with('success', 'Problem deleted successfully.');
    }

    /**
     * Preview unique problems from downtime erp or downtime erp2
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
                $uniqueProblems = DowntimeErp::whereNotNull('problemDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->distinct()
                    ->pluck('problemDowntime')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            } else {
                $uniqueProblems = DowntimeErp2::whereNotNull('problemDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->distinct()
                    ->pluck('problemDowntime')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            }

            $existingProblems = Problem::whereIn('name', $uniqueProblems->toArray())->pluck('name')->toArray();
            $newProblems = $uniqueProblems->filter(function($name) use ($existingProblems) {
                return !in_array($name, $existingProblems);
            })->values();

            return response()->json([
                'success' => true,
                'data_source' => $dataSource,
                'total_unique' => $uniqueProblems->count(),
                'existing_count' => count($existingProblems),
                'new_count' => $newProblems->count(),
                'sample_data' => $uniqueProblems->take(20)->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract unique problems from downtime erp or downtime erp2
     */
    public function extractFromDowntime(Request $request)
    {
        // Only allow admin (wahid@tpmcmms.id) to access this feature
        if (auth()->user()->email !== 'wahid@tpmcmms.id') {
            return redirect()->route('problems.index')
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
                // Get unique problems from downtime_erp
                $uniqueProblems = DowntimeErp::whereNotNull('problemDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->distinct()
                    ->pluck('problemDowntime')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            } else {
                // Get unique problems from downtime_erp2
                $uniqueProblems = DowntimeErp2::whereNotNull('problemDowntime')
                    ->where('problemDowntime', '!=', '')
                    ->distinct()
                    ->pluck('problemDowntime')
                    ->filter()
                    ->map(function($name) {
                        return trim($name);
                    })
                    ->unique()
                    ->values();
            }

            DB::beginTransaction();

            foreach ($uniqueProblems as $problemName) {
                if (empty($problemName)) {
                    continue;
                }

                // Check if problem already exists
                $existingProblem = Problem::where('name', $problemName)->first();

                if ($existingProblem) {
                    $skipped++;
                    continue;
                }

                // Create new problem
                try {
                    Problem::create([
                        'name' => $problemName,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create problem '{$problemName}': " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Extraction completed! Created: {$created}, Skipped: {$skipped}";
            if (!empty($errors)) {
                $message .= ". Errors: " . count($errors);
            }

            return redirect()->route('problems.index')
                ->with('success', $message)
                ->with('extraction_details', [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('problems.index')
                ->with('error', 'Extraction failed: ' . $e->getMessage());
        }
    }
}
