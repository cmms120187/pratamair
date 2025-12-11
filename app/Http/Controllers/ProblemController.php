<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Problem;
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
        
        $problems = $query->orderBy('problem_header', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(12);
        
        // Get filter options
        $systems = \App\Models\System::orderBy('nama_sistem')->get();
        $problemHeaders = Problem::whereNotNull('problem_header')->distinct()->orderBy('problem_header')->pluck('problem_header');
        
        return view('problems.index', compact('problems', 'systems', 'problemHeaders'));
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
}
