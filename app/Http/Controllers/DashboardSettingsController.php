<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardSettingsController extends Controller
{
    /**
     * Display dashboard settings page (admin only)
     */
    public function index()
    {
        // Only admin can access
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }
        
        $user = Auth::user();
        $settings = $user->getDashboardSettings();
        
        return view('dashboard-settings.index', [
            'settings' => $settings,
        ]);
    }
    
    /**
     * Update dashboard settings
     */
    public function update(Request $request)
    {
        // Only admin can update
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }
        
        $validated = $request->validate([
            'data_source' => 'required|in:downtime_erp2,downtime_erp,downtime',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);
        
        $user = Auth::user();
        $user->setDashboardSettings([
            'data_source' => $validated['data_source'],
            'month' => (int)$validated['month'],
            'year' => (int)$validated['year'],
        ]);
        
        return redirect()->route('dashboard-settings.index')
            ->with('success', 'Dashboard settings updated successfully!');
    }
}
