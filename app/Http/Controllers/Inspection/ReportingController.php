<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;

class ReportingController extends Controller
{
    public function index() { return view('inspection-templates.index'); }
    public function show($id) { return view('inspection-templates.show'); }
}
