<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ProblemMm;
class ProblemMmController extends Controller
{
    public function index()
    {
        $problemMms = ProblemMm::orderBy('name', 'asc')->paginate(12);
        return view('problem_mms.index', compact('problemMms'));
    }

    public function create()
    {
        return view('problem_mms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $problemMm = new ProblemMm();
        $problemMm->name = $validated['name'];
        $problemMm->save();
        return redirect()->route('problem-mms.index')->with('success', 'Problem MMS created successfully.');
    }

    public function edit($id)
    {
        $problemMm = ProblemMm::findOrFail($id);
        return view('problem_mms.edit', compact('problemMm'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $problemMm = ProblemMm::findOrFail($id);
        $problemMm->name = $validated['name'];
        $problemMm->save();
        return redirect()->route('problem-mms.index')->with('success', 'Problem MMS updated successfully.');
    }

    public function destroy($id)
    {
        $problemMm = ProblemMm::findOrFail($id);
        $problemMm->delete();
        return redirect()->route('problem-mms.index')->with('success', 'Problem MMS deleted successfully.');
    }
}
