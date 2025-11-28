<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Reason;
class ReasonController extends Controller
{
    public function index()
    {
        $reasons = Reason::orderBy('name', 'asc')->paginate(12);
        return view('reasons.index', compact('reasons'));
    }

    public function create()
    {
        return view('reasons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $reason = new Reason();
        $reason->name = $validated['name'];
        $reason->save();
        return redirect()->route('reasons.index')->with('success', 'Reason created successfully.');
    }

    public function edit($id)
    {
        $reason = Reason::findOrFail($id);
        return view('reasons.edit', compact('reason'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $reason = Reason::findOrFail($id);
        $reason->name = $validated['name'];
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
