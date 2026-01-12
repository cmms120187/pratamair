<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InspectionTemplateController extends Controller
{
    public function index()
    {
        // Minimal placeholder
        return view('inspection-templates.index', ['templates' => []]);
    }

    public function create(Request $request)
    {
        return view('inspection-templates.create');
    }

    public function store(Request $request)
    {
        // Not implemented - placeholder
        return redirect()->route('inspection-templates.index');
    }

    public function show($id)
    {
        return view('inspection-templates.show', ['template' => null]);
    }

    public function edit($id)
    {
        return view('inspection-templates.edit', ['template' => null]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('inspection-templates.index');
    }

    public function destroy($id)
    {
        return redirect()->route('inspection-templates.index');
    }

    public function getTemplateByMachineType(Request $request)
    {
        // Minimal response for AJAX: return empty payload
        return response()->json(['template' => null]);
    }
}
