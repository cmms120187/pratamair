<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Action;
class ActionController extends Controller
{
    public function index()
    {
        $actions = Action::orderBy('name', 'asc')->paginate(12);
        return view('actions.index', compact('actions'));
    }

    public function create()
    {
        return view('actions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $action = new Action();
        $action->name = $validated['name'];
        $action->save();
        return redirect()->route('actions.index')->with('success', 'Action created successfully.');
    }

    public function edit($id)
    {
        $action = Action::findOrFail($id);
        return view('actions.edit', compact('action'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $action = Action::findOrFail($id);
        $action->name = $validated['name'];
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
