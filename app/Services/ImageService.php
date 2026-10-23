<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Convert an uploaded image to WebP format and save to public storage disk.
     * Reduces file size by ~60-80% while retaining visual quality.
     *
     * @param UploadedFile $file
     * @param string $directory (default: 'products')
     * @param int $quality (1-100, default: 82)
     * @return string Relative storage path (e.g., 'products/abc123xyz.webp')
     */
    public static function storeAsWebp(UploadedFile $file, string $directory = 'products', int $quality = 82): string
    {
        // If GD extension or imagewebp is not available, fallback to standard upload
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return $file->store($directory, 'public');
        }

        try {
            $mime = $file->getMimeType();
            $realPath = $file->getRealPath();

            $image = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
                'image/png' => @imagecreatefrompng($realPath),
                'image/webp' => @imagecreatefromwebp($realPath),
                'image/gif' => @imagecreatefromgif($realPath),
                default => null,
            };

            // If image couldn't be loaded with GD, fallback to default storage
            if (!$image) {
                return $file->store($directory, 'public');
            }

            // Preserve alpha channel transparency for PNG/WebP
            imagealphablending($image, false);
            imagesavealpha($image, true);

            // Prepare destination filename and path in storage/app/public/
            $filename = Str::random(40) . '.webp';
            $relativeDir = trim($directory, '/');
            $fullDir = Storage::disk('public')->path($relativeDir);

            if (!file_exists($fullDir)) {
                @mkdir($fullDir, 0755, true);
            }

            $destination = $fullDir . DIRECTORY_SEPARATOR . $filename;

            // Output WebP
            $success = imagewebp($image, $destination, $quality);
            imagedestroy($image);

            if ($success && file_exists($destination)) {
                return $relativeDir . '/' . $filename;
            }

            // Fallback if writing failed
            return $file->store($directory, 'public');
        } catch (\Throwable $e) {
            \Log::warning("WebP conversion failed for {$file->getClientOriginalName()}: " . $e->getMessage());
            return $file->store($directory, 'public');
        }
    }
}
