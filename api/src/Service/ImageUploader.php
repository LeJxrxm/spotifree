<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader extends AbstractFileUploader
{
    /**
     * Allowed MIME types for image uploads (whitelist)
     */
    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',           // JPEG
        'image/jpg',            // JPG (alternative)
        'image/png',            // PNG
        'image/gif',            // GIF
        'image/webp',           // WebP
        'image/svg+xml',        // SVG
        'image/bmp',            // BMP
        'image/x-ms-bmp',       // BMP (alternative)
    ];

    public function upload(UploadedFile $file, string $subDirectory = 'images'): string
    {
        // Validate MIME type against whitelist
        $this->validateMimeType($file, self::ALLOWED_IMAGE_MIME_TYPES);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->projectDir . '/public/uploads/' . $subDirectory;
        
        $file->move($targetDirectory, $newFilename);

        return '/uploads/' . $subDirectory . '/' . $newFilename;
    }
}
