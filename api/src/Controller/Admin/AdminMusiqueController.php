<?php

namespace App\Controller\Admin;

use App\Entity\Musique;
use App\Repository\AlbumRepository;
use App\Repository\ArtisteRepository;
use App\Repository\MusiqueRepository;
use App\Service\AudioUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/musiques', name: 'api_admin_musiques_')]
#[IsGranted('ROLE_ADMIN')]
class AdminMusiqueController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(MusiqueRepository $musiqueRepository): JsonResponse
    {
        $musiques = $musiqueRepository->findAll();
        // Avoid circular reference
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
                ] : null,
            ];
        }, $musiques);

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $em, 
        AudioUploader $audioUploader,
        ArtisteRepository $artisteRepository,
        AlbumRepository $albumRepository
    ): JsonResponse {
        $titre = $request->request->get('titre');
        $artisteId = $request->request->get('artiste');
        $albumId = $request->request->get('album');
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['message' => 'File required'], 400);
        }

        try {
            $audioUrl = $audioUploader->uploadAndConvert($file);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        if (!$titre) {
            $titre = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        }

        $musique = new Musique();
        $musique->setTitre($titre);
        $musique->setAudioUrl($audioUrl);
        $musique->setDuree(0); // TODO: Get Duration

        if ($artisteId) {
            $artiste = $artisteRepository->find($artisteId);
            if ($artiste) $musique->setArtiste($artiste);
        }

        if ($albumId) {
            $album = $albumRepository->find($albumId);
            if ($album) $musique->setAlbum($album);
        }

        $em->persist($musique);
        $em->flush();

        return $this->json($musique, 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Musique $musique): JsonResponse
    {
         return $this->json([
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
                    'coverUrl' => $musique->getAlbum()->getCoverUrl(), // Useful context
                ] : null,
            ]);
    }

    #[Route('/{id}', name: 'update', methods: ['POST', 'PUT', 'PATCH'])]
    public function update(
        Musique $musique, 
        Request $request, 
        EntityManagerInterface $em,
        ArtisteRepository $artisteRepository,
        AlbumRepository $albumRepository
    ): JsonResponse {
        $titre = $request->request->get('titre');
        $artisteId = $request->request->get('artiste');
        $albumId = $request->request->get('album');
        
        if ($request->getContentTypeFormat() === 'json') {
             $data = json_decode($request->getContent(), true);
             $titre = $data['titre'] ?? $titre;
             $artisteId = $data['artiste'] ?? $artisteId;
             $albumId = $data['album'] ?? $albumId;
        }

        if ($titre) $musique->setTitre($titre);
        
        if ($artisteId) {
            $artiste = $artisteRepository->find($artisteId);
            if ($artiste) $musique->setArtiste($artiste);
        }

        if ($albumId) {
             $album = $albumRepository->find($albumId);
             if ($album) {
                 $musique->setAlbum($album);
             }
        } elseif ($albumId === null && ($request->request->has('album') || isset($data['album']))) {
             $musique->setAlbum(null);
        }

        $em->flush();

        return $this->json($musique);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Musique $musique, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($musique);
        $em->flush();

        return $this->json(['message' => 'Musique deleted']);
    }
}
