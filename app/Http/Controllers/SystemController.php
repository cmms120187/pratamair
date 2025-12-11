<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\System;

class SystemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $systems = System::orderBy('id', 'asc')->paginate(15);
        return view('machinary.sistem.index', compact('systems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('machinary.sistem.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_sistem' => 'required|string|max:255|unique:systems,nama_sistem',
            'deskripsi' => 'nullable|string',
        ]);

        System::create($validated);

        return redirect()->route('systems.index')->with('success', 'System created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $system = System::findOrFail($id);
        return view('machinary.sistem.show', compact('system'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $system = System::findOrFail($id);
        return view('machinary.sistem.edit', compact('system'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_sistem' => 'required|string|max:255|unique:systems,nama_sistem,' . $id,
            'deskripsi' => 'nullable|string',
        ]);

        $system = System::findOrFail($id);
        $system->update($validated);

        return redirect()->route('systems.index')->with('success', 'System updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $system = System::findOrFail($id);
        $system->delete();

        return redirect()->route('systems.index')->with('success', 'System deleted successfully.');
    }
}




