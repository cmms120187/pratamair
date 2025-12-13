<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageHelper
{
    /**
     * Resize image to fit within max dimensions while maintaining aspect ratio
     * 
     * @param resource $image Image resource
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return resource Resized image resource
     */
    private static function resizeImage($image, int $maxWidth, int $maxHeight)
    {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = (int)($originalWidth * $ratio);
        $newHeight = (int)($originalHeight * $ratio);
        
        // Create new image with new dimensions
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
        
        // Resize image
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        return $resized;
    }

    /**
     * Compress image to target file size (in bytes)
     * 
     * @param resource $image Image resource
     * @param string $outputPath Output file path
     * @param int $targetSize Target file size in bytes (default: 1MB)
     * @param int $maxWidth Maximum width (default: 1920)
     * @param int $maxHeight Maximum height (default: 1920)
     * @return bool Success status
     */
    private static function compressToTargetSize($image, string $outputPath, int $targetSize = 1048576, int $maxWidth = 1920, int $maxHeight = 1920): bool
    {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        // Resize if image is too large
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $image = self::resizeImage($image, $maxWidth, $maxHeight);
        }
        
        // Try different quality levels to reach target size
        $minQuality = 30;
        $maxQuality = 95;
        $bestQuality = 85;
        $bestSize = PHP_INT_MAX;
        
        // Binary search for optimal quality
        while ($maxQuality - $minQuality > 5) {
            $testQuality = (int)(($minQuality + $maxQuality) / 2);
            
            // Save to temporary file to check size
            $tempPath = $outputPath . '.tmp';
            @imagewebp($image, $tempPath, $testQuality);
            
            if (file_exists($tempPath)) {
                $fileSize = filesize($tempPath);
                
                if ($fileSize <= $targetSize) {
                    // File size is acceptable, try higher quality
                    $minQuality = $testQuality;
                    if ($fileSize < $bestSize) {
                        $bestSize = $fileSize;
                        $bestQuality = $testQuality;
                    }
                } else {
                    // File too large, need lower quality
                    $maxQuality = $testQuality;
                }
                
                @unlink($tempPath);
            } else {
                // Failed to save, reduce quality
                $maxQuality = $testQuality;
            }
        }
        
        // Save with best quality found
        $success = @imagewebp($image, $outputPath, $bestQuality);
        
        // If still too large, resize more aggressively
        if ($success && file_exists($outputPath) && filesize($outputPath) > $targetSize) {
            $currentSize = filesize($outputPath);
            $scaleFactor = sqrt($targetSize / $currentSize);
            $newWidth = (int)(imagesx($image) * $scaleFactor);
            $newHeight = (int)(imagesy($image) * $scaleFactor);
            
            if ($newWidth > 0 && $newHeight > 0) {
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefill($resized, 0, 0, $transparent);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($image), imagesy($image));
                
                @unlink($outputPath);
                $success = @imagewebp($resized, $outputPath, 85);
                imagedestroy($resized);
            }
        }
        
        return $success && file_exists($outputPath) && filesize($outputPath) > 0;
    }

    /**
     * Convert uploaded image to WebP format and store it
     * Automatically resizes and compresses to max 1MB
     * 
     * @param UploadedFile $file
     * @param string $directory
     * @param int $quality Quality from 0-100 (default: 85)
     * @param int $maxSize Maximum file size in bytes (default: 1MB = 1048576)
     * @return string|null Path to stored WebP image or null on failure
     */
    public static function convertToWebP(UploadedFile $file, string $directory, int $quality = 85, int $maxSize = 1048576): ?string
    {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                \Log::warning('ImageHelper: GD extension is not available, using fallback storage');
                // Fallback to original file storage
                $path = $file->store($directory, 'public');
                \Log::info('ImageHelper: File stored using fallback method', ['path' => $path]);
                return $path;
            }

            // Check if WebP is supported
            if (!function_exists('imagewebp')) {
                \Log::warning('ImageHelper: WebP support is not available in GD, using fallback storage');
                // Fallback to original file storage
                $path = $file->store($directory, 'public');
                \Log::info('ImageHelper: File stored using fallback method', ['path' => $path]);
                return $path;
            }

            // Get image info
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                \Log::warning('ImageHelper: Unable to get image info, using fallback storage');
                $path = $file->store($directory, 'public');
                \Log::info('ImageHelper: File stored using fallback method', ['path' => $path]);
                return $path;
            }

            $mimeType = $imageInfo['mime'];
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Create image resource based on MIME type
            $image = null;
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = @imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($file->getRealPath());
                    break;
                case 'image/gif':
                    $image = @imagecreatefromgif($file->getRealPath());
                    break;
                case 'image/webp':
                    // Already WebP, just store it
                    \Log::info('ImageHelper: File is already WebP, storing directly');
                    $path = $file->store($directory, 'public');
                    \Log::info('ImageHelper: WebP file stored', ['path' => $path]);
                    return $path;
                default:
                    \Log::warning('ImageHelper: Unsupported image type, using fallback storage', ['mime' => $mimeType]);
                    $path = $file->store($directory, 'public');
                    \Log::info('ImageHelper: File stored using fallback method', ['path' => $path]);
                    return $path;
            }

            if ($image === false) {
                \Log::warning('ImageHelper: Failed to create image resource, using fallback storage');
                $path = $file->store($directory, 'public');
                \Log::info('ImageHelper: File stored using fallback method', ['path' => $path]);
                return $path;
            }

            // Generate unique filename with .webp extension
            $filename = uniqid() . '_' . time() . '.webp';
            $path = $directory . '/' . $filename;
            $fullPath = storage_path('app/public/' . $path);

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    \Log::error('ImageHelper: Failed to create directory', ['dir' => $dir]);
                    imagedestroy($image);
                    $path = $file->store($directory, 'public');
                    \Log::info('ImageHelper: File stored using fallback method after directory creation failure', ['path' => $path]);
                    return $path;
                }
            }

            // Compress image to target size (max 1MB)
            $success = self::compressToTargetSize($image, $fullPath, $maxSize, 1920, 1920);

            // Free memory
            imagedestroy($image);

            if ($success && file_exists($fullPath) && filesize($fullPath) > 0) {
                $finalSize = filesize($fullPath);
                \Log::info('ImageHelper: Successfully converted, resized and compressed WebP image', [
                    'path' => $path,
                    'size' => $finalSize,
                    'size_mb' => round($finalSize / 1048576, 2) . ' MB'
                ]);
                return $path;
            } else {
                \Log::warning('ImageHelper: Failed to save WebP image or file is empty, using fallback storage', [
                    'success' => $success,
                    'exists' => file_exists($fullPath),
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0
                ]);
                // Clean up failed file if it exists
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                $path = $file->store($directory, 'public');
                \Log::info('ImageHelper: File stored using fallback method', ['path' => $path]);
                return $path;
            }
        } catch (\Exception $e) {
            \Log::error('ImageHelper: Exception during WebP conversion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Fallback to original file storage - ensure file is always saved
            try {
                $path = $file->store($directory, 'public');
                \Log::info('ImageHelper: File stored using fallback method after exception', ['path' => $path]);
                return $path;
            } catch (\Exception $fallbackException) {
                \Log::error('ImageHelper: Critical error - both WebP conversion and fallback storage failed', [
                    'original_error' => $e->getMessage(),
                    'fallback_error' => $fallbackException->getMessage()
                ]);
                return null;
            }
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

