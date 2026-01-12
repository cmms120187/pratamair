<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UpdatingController extends Controller
{
    public function index() { return view('inspections.index'); }
    public function create($scheduleId = null) { return view('inspections.create'); }
    public function store(Request $r) { return redirect()->route('inspections.updating.index'); }
    public function edit($id) { return view('inspections.edit'); }
    public function update(Request $r, $id) { return redirect()->route('inspections.updating.index'); }
}
