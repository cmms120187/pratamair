<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageHelper
{
    /**
     * Convert uploaded image to WebP format and store it
     * 
     * @param UploadedFile $file
     * @param string $directory
     * @param int $quality Quality from 0-100 (default: 85)
     * @return string|null Path to stored WebP image or null on failure
     */
    public static function convertToWebP(UploadedFile $file, string $directory, int $quality = 85): ?string
    {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                \Log::error('GD extension is not available for image conversion');
                // Fallback to original file storage
                return $file->store($directory, 'public');
            }

            // Check if WebP is supported
            if (!function_exists('imagewebp')) {
                \Log::error('WebP support is not available in GD');
                // Fallback to original file storage
                return $file->store($directory, 'public');
            }

            // Get image info
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                \Log::error('Unable to get image info');
                return $file->store($directory, 'public');
            }

            $mimeType = $imageInfo['mime'];
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Create image resource based on MIME type
            $image = null;
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($file->getRealPath());
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($file->getRealPath());
                    break;
                case 'image/webp':
                    // Already WebP, just store it
                    return $file->store($directory, 'public');
                default:
                    \Log::error('Unsupported image type: ' . $mimeType);
                    return $file->store($directory, 'public');
            }

            if ($image === false) {
                \Log::error('Failed to create image resource');
                return $file->store($directory, 'public');
            }

            // Generate unique filename with .webp extension
            $filename = uniqid() . '_' . time() . '.webp';
            $path = $directory . '/' . $filename;
            $fullPath = storage_path('app/public/' . $path);

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Convert and save as WebP
            $success = imagewebp($image, $fullPath, $quality);

            // Free memory
            imagedestroy($image);

            if ($success) {
                return $path;
            } else {
                \Log::error('Failed to save WebP image');
                return $file->store($directory, 'public');
            }
        } catch (\Exception $e) {
            \Log::error('Error converting image to WebP: ' . $e->getMessage());
            // Fallback to original file storage
            return $file->store($directory, 'public');
        }
    }

    /**
     * Delete old image if it exists
     * 
     * @param string|null $path
     * @return void
     */
    public static function deleteOldImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

