<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\System;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::with('systems')->orderBy('name', 'asc')->paginate(12);
        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        $systems = System::orderBy('nama_sistem', 'asc')->get();
        return view('groups.create', compact('systems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ]);

        $group = Group::create($request->only('name'));
        
        if ($request->has('systems')) {
            $group->systems()->sync($request->systems);
        }

        return redirect()->route('groups.index')->with('success', 'Group created successfully');
    }

    public function edit(Group $group)
    {
        $group->load('systems');
        $systems = System::orderBy('nama_sistem', 'asc')->get();
        return view('groups.edit', compact('group', 'systems'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'systems' => 'nullable|array',
            'systems.*' => 'exists:systems,id',
        ]);

        $group->update($request->only('name'));
        
        if ($request->has('systems')) {
            $group->systems()->sync($request->systems);
        } else {
            $group->systems()->sync([]);
        }

        return redirect()->route('groups.index')->with('success', 'Group updated successfully');
    }

    public function destroy(Group $group)
    {
        $group->systems()->detach();
        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Group deleted successfully');
    }
}
