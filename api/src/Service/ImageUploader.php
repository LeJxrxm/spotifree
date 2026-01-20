<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploader
{
    public function __construct(
        private SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    ) {
    }

    public function upload(UploadedFile $file, string $subDirectory = 'images'): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->projectDir . '/public/uploads/' . $subDirectory;
        
        $file->move($targetDirectory, $newFilename);

        return '/uploads/' . $subDirectory . '/' . $newFilename;
    }
}
