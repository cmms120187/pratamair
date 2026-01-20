<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PhotoController extends Controller
{
    /**
     * Display the photo
     */
    public function show($id)
    {
        $photo = Photo::findOrFail($id);
        
        if (!$photo->exists()) {
            abort(404, 'Photo not found');
        }

        $content = $photo->getContent();
        if (!$content) {
            abort(404, 'Photo file not found');
        }

        return response($content)
            ->header('Content-Type', $photo->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $photo->original_filename . '"')
            ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
    }

    /**
     * Download the photo
     */
    public function download($id)
    {
        $photo = Photo::findOrFail($id);
        
        if (!$photo->exists()) {
            abort(404, 'Photo not found');
        }

        $content = $photo->getContent();
        if (!$content) {
            abort(404, 'Photo file not found');
        }

        return response($content)
            ->header('Content-Type', $photo->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $photo->original_filename . '"');
    }

    /**
     * Upload and store photo to database
     */
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // Max 10MB
            'related_type' => 'nullable|string|max:255',
            'related_id' => 'nullable|integer',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $request->file('photo');
            $imageInfo = getimagesize($file->getRealPath());
            
            if ($imageInfo === false) {
                return back()->withErrors(['photo' => 'Invalid image file']);
            }

            // Use ImageHelper to convert to WebP and store
            $filePath = \App\Helpers\ImageHelper::convertToWebP($file, 'photos', 85);
            
            if (!$filePath) {
                return back()->withErrors(['photo' => 'Failed to save photo']);
            }

            // Get file info
            $storedFile = Storage::disk('public')->get($filePath);
            $fileSize = strlen($storedFile);
            $mimeType = Storage::disk('public')->mimeType($filePath);
            
            // If converted to WebP, mime type should be webp
            if (str_ends_with($filePath, '.webp')) {
                $mimeType = 'image/webp';
            }

            // Create photo record in database
            $photo = Photo::create([
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => basename($filePath),
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
                'related_type' => $request->related_type,
                'related_id' => $request->related_id,
                'description' => $request->description,
                'uploaded_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'photo_id' => $photo->id,
                'url' => route('photos.show', $photo->id),
                'message' => 'Photo uploaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading photo: ' . $e->getMessage());
            return back()->withErrors(['photo' => 'Error uploading photo: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete photo
     */
    public function destroy($id)
    {
        $photo = Photo::findOrFail($id);
        
        // Delete file from storage
        if ($photo->exists()) {
            Storage::disk('public')->delete($photo->file_path);
        }
        
        // Delete record from database
        $photo->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
    }
}
