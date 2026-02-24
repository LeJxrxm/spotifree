<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

abstract class AbstractFileUploader
{
    public function __construct(
        protected SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%')] protected string $projectDir
    ) {
    }

    /**
     * Validate file MIME type against a whitelist
     *
     * @param UploadedFile $file The uploaded file to validate
     * @param array<string> $allowedMimeTypes List of allowed MIME types (e.g., ['image/jpeg', 'image/png'])
     * @throws \InvalidArgumentException If MIME type is not allowed
     */
    protected function validateMimeType(UploadedFile $file, array $allowedMimeTypes): void
    {
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid file type "%s". Allowed types: %s',
                    $mimeType,
                    implode(', ', $allowedMimeTypes)
                )
            );
        }
    }
}
