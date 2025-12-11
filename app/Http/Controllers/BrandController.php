<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = \App\Models\Brand::orderBy('name', 'asc')->paginate(12);
        return view('machinary.brand.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('machinary.brand.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
            \App\Models\Brand::create([
                'name' => $request->name,
            ]);
            return redirect()->route('brands.index')->with('success', 'Brand created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $brand = \App\Models\Brand::findOrFail($id);
        return view('machinary.brand.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
            $brand = \App\Models\Brand::findOrFail($id);
            $brand->update([
                'name' => $request->name,
            ]);
            return redirect()->route('brands.index')->with('success', 'Brand updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = \App\Models\Brand::findOrFail($id);
        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully');
    }

    /**
     * Import brands from machine_erp table
     * This will create brands from unique brand_name in machine_erp
     * Handles case-insensitive duplicates and normalizes names
     */
    public function importFromMachineErp()
    {
        try {
            // Get all brand_name from machine_erp
            $allBrandNames = \App\Models\MachineErp::whereNotNull('brand_name')
                ->where('brand_name', '!=', '')
                ->pluck('brand_name')
                ->toArray();
            
            // Normalize and group by case-insensitive name
            $normalizedMap = [];
            foreach ($allBrandNames as $brandName) {
                $normalized = trim($brandName);
                if (empty($normalized)) {
                    continue;
                }
                
                // Use lowercase for comparison, but keep original for display
                $key = strtolower($normalized);
                if (!isset($normalizedMap[$key])) {
                    $normalizedMap[$key] = [
                        'original' => $normalized,
                        'variations' => []
                    ];
                }
                
                // Track all variations
                if (!in_array($normalized, $normalizedMap[$key]['variations'])) {
                    $normalizedMap[$key]['variations'][] = $normalized;
                }
            }
            
            $created = 0;
            $skipped = 0;
            
            foreach ($normalizedMap as $key => $data) {
                $normalizedName = $data['original'];
                
                // Check if brand already exists (case-insensitive)
                $existing = \App\Models\Brand::whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])->first();
                
                if (!$existing) {
                    \App\Models\Brand::create([
                        'name' => $normalizedName,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }
            
            $message = "Imported $created new brands from machine_erp. $skipped already existed.";
            
            return redirect()->route('brands.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error importing brands from machine_erp: ' . $e->getMessage());
            return redirect()->route('brands.index')->withErrors(['error' => 'Error importing brands: ' . $e->getMessage()]);
        }
    }

    /**
     * Merge duplicate brands (case-insensitive)
     * This will merge duplicates and update all related records
     */
    public function mergeDuplicates()
    {
        try {
            // Get all brands
            $allBrands = \App\Models\Brand::all();
            
            // Group by lowercase name
            $grouped = [];
            foreach ($allBrands as $brand) {
                $key = strtolower(trim($brand->name));
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [];
                }
                $grouped[$key][] = $brand;
            }
            
            $merged = 0;
            $deleted = 0;
            
            foreach ($grouped as $key => $brands) {
                if (count($brands) > 1) {
                    // Keep the first one (or the one with most models)
                    usort($brands, function($a, $b) {
                        $aScore = $a->models()->count();
                        $bScore = $b->models()->count();
                        return $bScore <=> $aScore;
                    });
                    
                    $keep = $brands[0];
                    $duplicates = array_slice($brands, 1);
                    
                    // Update all related records to use the kept brand
                    foreach ($duplicates as $duplicate) {
                        // Update models
                        \App\Models\Model::where('brand_id', $duplicate->id)
                            ->update(['brand_id' => $keep->id]);
                        
                        // Update machines if exists
                        if (\Schema::hasTable('machines')) {
                            \DB::table('machines')
                                ->where('brand_id', $duplicate->id)
                                ->update(['brand_id' => $keep->id]);
                        }
                        
                        // Update name if kept one is uppercase but duplicate has better case
                        if (strtoupper($keep->name) === $keep->name && strtolower($duplicate->name) !== $duplicate->name) {
                            $keep->update(['name' => $duplicate->name]);
                        }
                        
                        // Delete duplicate
                        $duplicate->delete();
                        $deleted++;
                    }
                    
                    $merged++;
                }
            }
            
            $message = "Merged $merged groups of duplicates. $deleted duplicate brands deleted.";
            return redirect()->route('brands.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error merging duplicate brands: ' . $e->getMessage());
            return redirect()->route('brands.index')->withErrors(['error' => 'Error merging duplicates: ' . $e->getMessage()]);
        }
    }
}
