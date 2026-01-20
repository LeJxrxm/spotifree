<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{
    #[Route('/media/{type}/{filename}', name: 'media_serve', requirements: ['type' => 'albums|artistes|music', 'filename' => '.+'])]
    public function serve(string $type, string $filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $type . '/' . $filename;
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }
        
        $response = new BinaryFileResponse($filePath);
        
        // Add CORS headers - Support for web, Capacitor, and Ionic origins
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', '*');
        $response->headers->set('Access-Control-Max-Age', '3600');
        
        // Important pour Capacitor: permettre les credentials
        // Note: avec '*' on ne peut pas utiliser credentials, mais pour usage local c'est ok
        
        // Set content type based on file extension
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'flac' => 'audio/flac',
            'ogg' => 'audio/ogg',
        ];
        
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (isset($mimeTypes[$extension])) {
            $response->headers->set('Content-Type', $mimeTypes[$extension]);
        }
        
        // Cache control pour performances
        $response->setMaxAge(3600);
        $response->setPublic();
        
        return $response;
    }
}
