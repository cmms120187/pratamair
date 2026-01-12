<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SchedulingController extends Controller
{
    public function index() { return view('inspection-templates.index'); }
    public function create() { return view('inspection-templates.create'); }
    public function store(Request $r) { return redirect()->route('inspections.scheduling.index'); }
    public function show($id) { return redirect()->route('inspections.scheduling.index'); }
    public function edit($id) { return redirect()->route('inspections.scheduling.index'); }
    public function update(Request $r, $id) { return redirect()->route('inspections.scheduling.index'); }
    public function destroy($id) { return redirect()->route('inspections.scheduling.index'); }
    public function updatePic(Request $r) { return redirect()->route('inspections.scheduling.index'); }
}
