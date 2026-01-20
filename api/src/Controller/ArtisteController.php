<?php

namespace App\Controller;

use App\Repository\ArtisteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ArtisteController extends AbstractController
{
    #[Route('/api/artistes', name: 'api_artistes_index', methods: ['GET'])]
    public function index(ArtisteRepository $artisteRepository, Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        
        if ($search) {
            $artistes = $artisteRepository->createQueryBuilder('a')
                ->where('LOWER(a.nom) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult();
        } else {
            $artistes = $artisteRepository->findAll();
        }

        $data = array_map(function ($artiste) {
            return [
                'id' => $artiste->getId(),
                'nom' => $artiste->getNom(),
                'imageUrl' => $artiste->getImageUrl(),
            ];
        }, $artistes);

        return $this->json($data);
    }

    #[Route('/api/artistes/{id}', name: 'api_artistes_show', methods: ['GET'])]
    public function show(ArtisteRepository $artisteRepository, int $id): JsonResponse
    {
        $artiste = $artisteRepository->find($id);

        if (!$artiste) {
            return $this->json(['message' => 'Artist not found'], 404);
        }

        // Get popular tracks (for now just take the first 5)
        $popularTracks = $artiste->getMusiques()->slice(0, 5);

        $tracksData = array_map(function ($track) {
            return [
                'id' => $track->getId(),
                'titre' => $track->getTitre(),
                'duree' => $track->getDuree(),
                'audioUrl' => $track->getAudioUrl(),
                'artiste' => [
                    'id' => $track->getArtiste()->getId(),
                    'nom' => $track->getArtiste()->getNom(),
                    'imageUrl' => $track->getArtiste()->getImageUrl()
                ],
                'album' => $track->getAlbum() ? [
                    'id' => $track->getAlbum()->getId(),
                    'titre' => $track->getAlbum()->getTitre(),
                    'coverUrl' => $track->getAlbum()->getCoverUrl()
                ] : null
            ];
        }, $popularTracks);

        // Get albums
        $albumsData = array_map(function ($album) {
            return [
                'id' => $album->getId(),
                'titre' => $album->getTitre(),
                'coverUrl' => $album->getCoverUrl(),
                'createdAt' => $album->getCreatedAt() ? $album->getCreatedAt()->format('Y-m-d H:i:s') : null,
                // View fields can stay if helpful for frontend routing, but let's stick closer to entity for data
                'description' => ($album->getCreatedAt() ? $album->getCreatedAt()->format('Y') : '') . ' • Album',
            ];
        }, $artiste->getAlbums()->toArray());

        return $this->json([
            'id' => $artiste->getId(),
            'nom' => $artiste->getNom(), // Was 'name'
            'imageUrl' => $artiste->getImageUrl(),
            'listeners' => '1,234,567 monthly listeners', // Mock data
            'verified' => true, // Mock data
            'popularTracks' => $tracksData,
            'albums' => $albumsData
        ]);
    }
}
