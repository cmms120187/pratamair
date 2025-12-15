<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Action;
use App\Models\System;
use App\Models\Problem;
use App\Models\Reason;

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
}
