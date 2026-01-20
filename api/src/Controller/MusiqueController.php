<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Artiste;
use App\Entity\Musique;
use App\Repository\AlbumRepository;
use App\Repository\ArtisteRepository;
use App\Repository\MusiqueRepository;
use App\Service\AudioUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MusiqueController extends AbstractController
{
    #[Route('/api/musiques', name: 'api_musiques_index', methods: ['GET'])]
    public function index(MusiqueRepository $musiqueRepository, Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        
        if ($search) {
            $musiques = $musiqueRepository->createQueryBuilder('m')
                ->where('LOWER(m.titre) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult();
        } else {
            $musiques = $musiqueRepository->findAll();
        }

        $data = array_map(function ($musique) {
            return [
                'id' => $musique->getId(),
                'titre' => $musique->getTitre(),
                'duree' => $musique->getDuree(),
                'audioUrl' => $musique->getAudioUrl(),
                'artiste' => $musique->getArtiste() ? [
                    'id' => $musique->getArtiste()->getId(),
                    'nom' => $musique->getArtiste()->getNom(),
                ] : null,
                'album' => $musique->getAlbum() ? [
                    'id' => $musique->getAlbum()->getId(),
                    'titre' => $musique->getAlbum()->getTitre(),
                    'coverUrl' => $musique->getAlbum()->getCoverUrl(),
                ] : null,
            ];
        }, $musiques);

        return $this->json($data);
    }

    #[Route('/api/musiques', name: 'api_musiques_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ArtisteRepository $artisteRepository,
        AlbumRepository $albumRepository,
        AudioUploader $audioUploader
    ): JsonResponse {
        // Check for empty request which often means post_max_size exceeded
        if (empty($_FILES) && empty($_POST) && $request->headers->get('CONTENT_LENGTH') > 0) {
            return $this->json([
                'message' => 'Upload failed: File too large (exceeds post_max_size)',
                'limit' => ini_get('post_max_size')
            ], 413);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $titre = $request->request->get('titre');
        $artisteInput = $request->request->get('artiste'); // ID or Name
        $albumInput = $request->request->get('album'); // ID or Name

        if (!$file) {
            return $this->json(['message' => 'Missing file'], 400);
        }

        if (!$file->isValid()) {
            return $this->json([
                'message' => 'Upload failed',
                'error' => $file->getErrorMessage()
            ], 400);
        }

        try {
            $audioUrl = $audioUploader->uploadAndConvert($file);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Processing failed: ' . $e->getMessage()], 500);
        }
        
        // 4. Create Musique
        if (!$titre) {
            $titre = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        }
        
        $musique = new Musique();
        $musique->setTitre($titre);
        $musique->setAudioUrl($audioUrl);
        
        // Get duration (Mock logic for now, or need getid3 lib)
        $musique->setDuree(0); 
        
        // 1. Handle Artiste
        $artiste = null;
        if ($artisteInput) {
            if (is_numeric($artisteInput)) {
                $artiste = $artisteRepository->find($artisteInput);
            } else {
                $artiste = $artisteRepository->findOneBy(['nom' => $artisteInput]);
            }
            
            if (!$artiste && is_string($artisteInput)) {
                // Create new Artiste
                $artiste = new Artiste();
                $artiste->setNom($artisteInput);
                // Placeholder image or logic to upload artist image
                $artiste->setImageUrl('https://placehold.co/400?text=' . urlencode($artisteInput));
                $entityManager->persist($artiste);
            }
        }
        if ($artiste) {
             $musique->setArtiste($artiste);
        }

        // 2. Handle Album
        if ($albumInput && $artiste) {
            $album = null;
            if (is_numeric($albumInput)) {
                $album = $albumRepository->find($albumInput);
            } else {
                $album = $albumRepository->findOneBy(['titre' => $albumInput, 'artiste' => $artiste]);
            }

            if (!$album && is_string($albumInput)) {
                // Create new Album
                $album = new Album();
                $album->setTitre($albumInput);
                $album->setArtiste($artiste);
                $album->setCoverUrl('https://placehold.co/400?text=' . urlencode($albumInput));
                $entityManager->persist($album);
            }
             if ($album) {
                $musique->setAlbum($album);
            }
        }

        $entityManager->persist($musique);
        $entityManager->flush();

        return $this->json([
            'id' => $musique->getId(),
            'message' => 'Track uploaded successfully'
        ], 201);
    }

    #[Route('/api/musiques/youtube', name: 'api_musiques_create_youtube', methods: ['POST'])]
    public function createFromYouTube(
        Request $request,
        EntityManagerInterface $entityManager,
        ArtisteRepository $artisteRepository,
        AlbumRepository $albumRepository,
        AudioUploader $audioUploader
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $youtubeUrl = $data['youtubeUrl'] ?? null;
        $artisteInput = $data['artiste'] ?? null;
        $albumInput = $data['album'] ?? null;
        $titre = $data['titre'] ?? null;

        if (!$youtubeUrl) {
            return $this->json(['message' => 'Missing YouTube URL'], 400);
        }

        try {
            // Download from YouTube
            $downloadResult = $audioUploader->downloadFromYouTube($youtubeUrl);
            
            // Use provided title or fallback to YouTube title
            $finalTitle = $titre ?: $downloadResult['title'];
            
            $musique = new Musique();
            $musique->setTitre($finalTitle);
            $musique->setAudioUrl($downloadResult['audioUrl']);
            $musique->setDuree($downloadResult['duration']);
            
            // Handle Artiste
            $artiste = null;
            if ($artisteInput) {
                if (is_numeric($artisteInput)) {
                    $artiste = $artisteRepository->find($artisteInput);
                } else {
                    $artiste = $artisteRepository->findOneBy(['nom' => $artisteInput]);
                }
                
                if (!$artiste && is_string($artisteInput)) {
                    $artiste = new Artiste();
                    $artiste->setNom($artisteInput);
                    $artiste->setImageUrl('https://placehold.co/400?text=' . urlencode($artisteInput));
                    $entityManager->persist($artiste);
                }
            }
            if ($artiste) {
                $musique->setArtiste($artiste);
            }

            // Handle Album
            if ($albumInput && $artiste) {
                $album = null;
                if (is_numeric($albumInput)) {
                    $album = $albumRepository->find($albumInput);
                } else {
                    $album = $albumRepository->findOneBy(['titre' => $albumInput, 'artiste' => $artiste]);
                }

                if (!$album && is_string($albumInput)) {
                    $album = new Album();
                    $album->setTitre($albumInput);
                    $album->setArtiste($artiste);
                    $album->setCoverUrl('https://placehold.co/400?text=' . urlencode($albumInput));
                    $entityManager->persist($album);
                }
                if ($album) {
                    $musique->setAlbum($album);
                }
            }

            $entityManager->persist($musique);
            $entityManager->flush();

            return $this->json([
                'id' => $musique->getId(),
                'message' => 'Track downloaded from YouTube successfully',
                'title' => $finalTitle,
                'duration' => $downloadResult['duration']
            ], 201);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Download failed: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/musiques/{id}', name: 'api_musiques_update', methods: ['PATCH', 'PUT'])]
    public function update(
        Musique $musique,
        Request $request,
        EntityManagerInterface $entityManager,
        ArtisteRepository $artisteRepository,
        AlbumRepository $albumRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $titre = $data['titre'] ?? null;
        $artisteInput = $data['artiste'] ?? null;
        $albumInput = $data['album'] ?? null;

        if ($titre) {
            $musique->setTitre($titre);
        }

        // 1. Handle Artiste
        $artiste = null;
        if ($artisteInput) {
            if (is_numeric($artisteInput)) {
                $artiste = $artisteRepository->find($artisteInput);
            } else {
                $artiste = $artisteRepository->findOneBy(['nom' => $artisteInput]);
            }
            
            if (!$artiste && is_string($artisteInput)) {
                // Create new Artiste
                $artiste = new Artiste();
                $artiste->setNom($artisteInput);
                $artiste->setImageUrl('https://placehold.co/400?text=' . urlencode($artisteInput));
                $entityManager->persist($artiste);
            }
            $musique->setArtiste($artiste);
        }

        // 2. Handle Album
        if ($albumInput && $artiste) {
            $album = null;
            if (is_numeric($albumInput)) {
                $album = $albumRepository->find($albumInput);
            } else {
                $album = $albumRepository->findOneBy(['titre' => $albumInput, 'artiste' => $artiste]);
            }

            if (!$album && is_string($albumInput)) {
                // Create new Album
                $album = new Album();
                $album->setTitre($albumInput);
                $album->setArtiste($artiste);
                $album->setCoverUrl('https://placehold.co/400?text=' . urlencode($albumInput));
                $entityManager->persist($album);
            }
            $musique->setAlbum($album);
        } elseif ($albumInput === null && array_key_exists('album', $data)) {
            // Explicitly set to null if needed?
            $musique->setAlbum(null);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $musique->getId(),
            'message' => 'Track updated successfully'
        ]);
    }
}
