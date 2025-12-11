<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Reason;
use App\Models\System;
use App\Models\Problem;

class ReasonController extends Controller
{
    public function index()
    {
        $reasons = Reason::orderBy('name', 'asc')->paginate(12);
        return view('reasons.index', compact('reasons'));
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
}
