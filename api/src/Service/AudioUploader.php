<?php

namespace App\Service;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\DefaultAudio;
use FFMpeg\Format\Video\Ogg;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AudioUploader
{
    public function __construct(
        private SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    ) {}

    /**
     * Download audio from YouTube URL and convert to opus
     */
    public function downloadFromYouTube(string $youtubeUrl): array
    {
        // Validate YouTube URL
        if (!$this->isValidYouTubeUrl($youtubeUrl)) {
            throw new \InvalidArgumentException('Invalid YouTube URL');
        }

        $musicDir = $this->projectDir . '/public/uploads/music';
        
        // Ensure directory exists
        if (!is_dir($musicDir)) {
            mkdir($musicDir, 0755, true);
        }
        
        $tempFilename = 'yt-' . uniqid();
        $outputTemplate = $musicDir . '/' . $tempFilename . '.%(ext)s';

        // Use yt-dlp to download as opus directly
        $process = new Process([
            'yt-dlp',
            '--extract-audio',
            '--audio-format', 'opus',
            '--audio-quality', '64K',
            '--output', $outputTemplate,
            '--print', 'after_move:filepath', // Get final filename
            '--print', 'title', // Get video title
            '--print', 'duration', // Get duration in seconds
            '--no-playlist',
            $youtubeUrl
        ]);

        $process->setTimeout(300); // 5 minutes max
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            throw new \RuntimeException('yt-dlp failed: ' . $errorOutput);
        }

        $output = trim($process->getOutput());
        $lines = explode("\n", $output);
        
        // Order is: title, duration, filepath (not the other way around!)
        $title = $lines[count($lines) - 3] ?? 'Unknown';
        $duration = (int)($lines[count($lines) - 2] ?? 0);
        $filePath = $lines[count($lines) - 1] ?? null;

        if (!$filePath || !file_exists($filePath)) {
            throw new \RuntimeException('Failed to download audio from YouTube. FilePath: ' . ($filePath ?? 'null'));
        }

        // Get just the filename from full path
        $filename = basename($filePath);

        return [
            'audioUrl' => '/uploads/music/' . $filename,
            'title' => $title,
            'duration' => $duration
        ];
    }

    /**
     * Validate YouTube URL
     */
    private function isValidYouTubeUrl(string $url): bool
    {
        $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)[\w-]+/';
        return preg_match($pattern, $url) === 1;
    }

    /**
     * Upload and convert a file to opus
     */
    public function uploadAndConvert(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.opus';
        $destinationPath = $this->projectDir . '/public/uploads/music/' . $newFilename;

        $ffmpeg = FFMpeg::create();
        $audio = $ffmpeg->open($file->getPathname());

        $format = new Ogg(); // Suggestion LLM, à creuser si ok
        $format
            // ->setAudioCodec('libopus')
            ->setAudioChannels(2)
            ->setAudioKiloBitrate(64)
            ->setAdditionalParameters([
                '-c:a', 'libopus', // patch pour la ligne commentée au dessus qui est bloquante
                '-vbr', 'on',
                '-compression_level', '10',
            ])
        ;

        $audio->save($format, $destinationPath);

        return '/uploads/music/' . $newFilename;
    }
}
