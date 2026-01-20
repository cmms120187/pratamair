<?php

namespace App\Http\Controllers;

use App\Models\Standard;
use App\Models\MachineType;
use App\Models\StandardPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StandardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $standards = Standard::with('machineTypes', 'photos')
            ->orderBy('name', 'asc')
            ->paginate(8);
        
        return view('standards.index', compact('standards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machineTypes = MachineType::orderBy('name')->get();
        $standardPhotos = StandardPhoto::orderBy('name')->orderBy('created_at', 'desc')->get();
        return view('standards.create', compact('machineTypes', 'standardPhotos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference_type' => 'nullable|string|max:255',
            'reference_code' => 'nullable|string|max:255',
            'reference_name' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'target_value' => 'nullable|numeric',
            'description' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_name' => 'nullable|string|max:255',
            'standard_photo_ids' => 'nullable|array',
            'standard_photo_ids.*' => 'exists:standard_photos,id',
            'machine_type_ids' => 'nullable|array',
            'machine_type_ids.*' => 'exists:machine_types,id',
            'status' => 'required|in:active,inactive',
            'variants' => 'nullable|array|max:4',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.min_value' => 'required|numeric',
            'variants.*.max_value' => 'required|numeric',
            'variants.*.color' => 'required|string|max:7',
            'variants.*.order' => 'required|integer|min:1',
        ]);

        // Handle photo upload - Save to database using Photo model
        $photoId = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoModel = \App\Helpers\ImageHelper::saveToDatabase(
                $photo,
                'standards',
                'standard',
                null, // Will be set after standard is created
                'Photo for Standard'
            );
            if ($photoModel) {
                $photoId = $photoModel->id;
            }
        }

        $machineTypeIds = $validated['machine_type_ids'] ?? [];
        unset($validated['machine_type_ids']);

        $standardPhotoIds = $validated['standard_photo_ids'] ?? [];
        unset($validated['standard_photo_ids']);
        
        // Set photo_id if photo was uploaded
        if ($photoId) {
            $validated['photo_id'] = $photoId;
        }
        
        $standard = Standard::create($validated);
        
        // Update photo related_id after standard is created
        if ($photoId) {
            $photoModel = \App\Models\Photo::find($photoId);
            if ($photoModel) {
                $photoModel->update([
                    'related_id' => $standard->id
                ]);
            }
        }
        
        // Attach machine types
        if (!empty($machineTypeIds)) {
            $standard->machineTypes()->attach($machineTypeIds);
        }

        // Handle selected existing photos from standard_photos (legacy many-to-many)
        // Note: This is for backward compatibility with StandardPhoto system
        if (!empty($standardPhotoIds)) {
            $standard->photos()->syncWithoutDetaching($standardPhotoIds);
        }

        // Handle variants (for new standard, all variants are new)
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $variantData) {
                \App\Models\StandardVariant::create([
                    'standard_id' => $standard->id,
                    'name' => $variantData['name'],
                    'min_value' => $variantData['min_value'],
                    'max_value' => $variantData['max_value'],
                    'color' => $variantData['color'],
                    'order' => $variantData['order'],
                ]);
            }
        }

        return redirect()->route('standards.index')
            ->with('success', 'Standard berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $standard = Standard::with('machineTypes', 'predictiveMaintenanceSchedules')->findOrFail($id);
        return view('standards.show', compact('standard'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $standard = Standard::with('photos')->findOrFail($id);
        $machineTypes = MachineType::orderBy('name')->get();
        $standardPhotos = StandardPhoto::orderBy('name')->orderBy('created_at', 'desc')->get();
        return view('standards.edit', compact('standard', 'machineTypes', 'standardPhotos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference_type' => 'nullable|string|max:255',
            'reference_code' => 'nullable|string|max:255',
            'reference_name' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'target_value' => 'nullable|numeric',
            'description' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_name' => 'nullable|string|max:255',
            'standard_photo_ids' => 'nullable|array',
            'standard_photo_ids.*' => 'exists:standard_photos,id',
            'machine_type_ids' => 'nullable|array',
            'machine_type_ids.*' => 'exists:machine_types,id',
            'status' => 'required|in:active,inactive',
            'variants' => 'nullable|array|max:4',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.min_value' => 'required|numeric',
            'variants.*.max_value' => 'required|numeric',
            'variants.*.color' => 'required|string|max:7',
            'variants.*.order' => 'required|integer|min:1',
            'variants.*.id' => 'nullable|exists:standard_variants,id',
        ]);

        $standard = Standard::with('photos')->findOrFail($id);
        
        // Handle photo upload - Save to database using Photo model
        if ($request->hasFile('photo')) {
            // Delete old photo from database if exists
            if ($standard->photo_id) {
                \App\Helpers\ImageHelper::deletePhotoFromDatabase($standard->photo_id);
            }
            // Also delete old photo file if exists (legacy)
            if ($standard->photo && Storage::disk('public')->exists($standard->photo)) {
                Storage::disk('public')->delete($standard->photo);
            }
            
            $photo = $request->file('photo');
            $photoModel = \App\Helpers\ImageHelper::saveToDatabase(
                $photo,
                'standards',
                'standard',
                $standard->id,
                'Photo for Standard'
            );
            if ($photoModel) {
                $validated['photo_id'] = $photoModel->id;
                $validated['photo'] = null; // Clear legacy path
            }
        }
        
        $machineTypeIds = $validated['machine_type_ids'] ?? [];
        unset($validated['machine_type_ids']);

        $standardPhotoIds = $validated['standard_photo_ids'] ?? [];
        unset($validated['standard_photo_ids']);
        
        $standard->update($validated);
        
        // Sync machine types
        $standard->machineTypes()->sync($machineTypeIds);

        // Handle new photo upload (multiple photos)
        $newPhotoId = null;
        if ($request->hasFile('photo') && $request->filled('photo_name')) {
            $newPhoto = StandardPhoto::create([
                'standard_id' => null, // Photo tidak langsung terikat ke standard, akan di-attach via pivot
                'photo_path' => $validated['photo'],
                'name' => $request->photo_name,
            ]);
            $newPhotoId = $newPhoto->id;
        } elseif ($request->hasFile('photo')) {
            // If no name provided, use the file name
            $newPhoto = StandardPhoto::create([
                'standard_id' => null,
                'photo_path' => $validated['photo'],
                'name' => $request->file('photo')->getClientOriginalName(),
            ]);
            $newPhotoId = $newPhoto->id;
        }

        // Sync selected existing photos (many-to-many)
        // sync() akan menghapus photo yang tidak dipilih, tapi tidak menghapus photo dari standard lain
        if (!empty($standardPhotoIds)) {
            $standard->photos()->sync($standardPhotoIds);
        } else {
            // If no photos selected, remove all photos from this standard only
            $standard->photos()->detach();
        }
        
        // Attach new photo if uploaded
        if ($newPhotoId) {
            $standard->photos()->syncWithoutDetaching([$newPhotoId]);
        }

        // Handle variants
        if ($request->has('variants') && is_array($request->variants)) {
            $variantIds = [];
            foreach ($request->variants as $variantData) {
                if (isset($variantData['id']) && $variantData['id']) {
                    // Update existing variant
                    $variant = \App\Models\StandardVariant::find($variantData['id']);
                    if ($variant && $variant->standard_id == $standard->id) {
                        $variant->update([
                            'name' => $variantData['name'],
                            'min_value' => $variantData['min_value'],
                            'max_value' => $variantData['max_value'],
                            'color' => $variantData['color'],
                            'order' => $variantData['order'],
                        ]);
                        $variantIds[] = $variant->id;
                    }
                } else {
                    // Create new variant
                    $variant = \App\Models\StandardVariant::create([
                        'standard_id' => $standard->id,
                        'name' => $variantData['name'],
                        'min_value' => $variantData['min_value'],
                        'max_value' => $variantData['max_value'],
                        'color' => $variantData['color'],
                        'order' => $variantData['order'],
                    ]);
                    $variantIds[] = $variant->id;
                }
            }
            // Delete variants that are not in the request
            \App\Models\StandardVariant::where('standard_id', $standard->id)
                ->whereNotIn('id', $variantIds)
                ->delete();
        } else {
            // If no variants provided, delete all existing variants
            \App\Models\StandardVariant::where('standard_id', $standard->id)->delete();
        }

        return redirect()->route('standards.index')
            ->with('success', 'Standard berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $standard = Standard::findOrFail($id);
        
        // Check if standard is used in any schedules
        if ($standard->predictiveMaintenanceSchedules()->count() > 0) {
            return redirect()->route('standards.index')
                ->with('error', 'Tidak dapat menghapus standard yang sedang digunakan dalam jadwal.');
        }
        
        // Delete photo if exists
        if ($standard->photo && Storage::disk('public')->exists($standard->photo)) {
            Storage::disk('public')->delete($standard->photo);
        }
        
        $standard->delete();

        return redirect()->route('standards.index')
            ->with('success', 'Standard berhasil dihapus.');
    }
}
