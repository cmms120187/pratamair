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
        ]);

        // Handle photo upload (legacy single photo)
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('standards', 'public');
        }

        $machineTypeIds = $validated['machine_type_ids'] ?? [];
        unset($validated['machine_type_ids']);

        $standardPhotoIds = $validated['standard_photo_ids'] ?? [];
        unset($validated['standard_photo_ids']);

        $standard = Standard::create($validated);
        
        // Attach machine types
        if (!empty($machineTypeIds)) {
            $standard->machineTypes()->attach($machineTypeIds);
        }

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

        // Attach selected existing photos (many-to-many, tidak akan menghapus dari standard lain)
        if (!empty($standardPhotoIds)) {
            $standard->photos()->syncWithoutDetaching($standardPhotoIds);
        }
        
        // Attach new photo if uploaded
        if ($newPhotoId) {
            $standard->photos()->syncWithoutDetaching([$newPhotoId]);
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
        ]);

        $standard = Standard::with('photos')->findOrFail($id);
        
        // Handle photo upload (legacy single photo)
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($standard->photo && Storage::disk('public')->exists($standard->photo)) {
                Storage::disk('public')->delete($standard->photo);
            }
            $validated['photo'] = $request->file('photo')->store('standards', 'public');
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
