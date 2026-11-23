<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Securely store an uploaded listing image on the public disk.
     */
    public function uploadListingImage(UploadedFile $file, ?string $oldPath = null): string
    {
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $extension = $file->guessExtension() ?: 'jpg';
        $filename = 'listing_' . Str::uuid()->toString() . '.' . $extension;

        return $file->storeAs('listings', $filename, 'public');
    }

    /**
     * Delete a stored image if it exists.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
