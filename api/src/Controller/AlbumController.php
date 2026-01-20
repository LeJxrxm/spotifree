<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('/api/albums', name: 'api_albums_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository, Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        
        if ($search) {
            $albums = $albumRepository->createQueryBuilder('a')
                ->where('LOWER(a.titre) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult();
        } else {
            $albums = $albumRepository->findAll();
        }

        $data = array_map(function ($album) {
            return [
                'id' => $album->getId(),
                'titre' => $album->getTitre(),
                'coverUrl' => $album->getCoverUrl(),
                'createdAt' => $album->getCreatedAt()?->format('c'),
                'artiste' => $album->getArtiste() ? [
                    'id' => $album->getArtiste()->getId(),
                    'nom' => $album->getArtiste()->getNom(),
                    'imageUrl' => $album->getArtiste()->getImageUrl(),
                ] : null,
            ];
        }, $albums);

        return $this->json($data);
    }

    #[Route('/api/albums/{id}', name: 'api_albums_show', methods: ['GET'])]
    public function show(AlbumRepository $albumRepository, int $id): JsonResponse
    {
        $album = $albumRepository->find($id);

        if (!$album) {
            return $this->json(['message' => 'Album not found'], 404);
        }

        $musiquesData = array_map(function ($musique) use ($album) {
            return [
                'id' => $musique->getId(),
                'titre' => $musique->getTitre(),
                'duree' => $musique->getDuree(),
                'audioUrl' => $musique->getAudioUrl(),
                'artiste' => $musique->getArtiste() ? [
                    'id' => $musique->getArtiste()->getId(),
                    'nom' => $musique->getArtiste()->getNom(),
                    'imageUrl' => $musique->getArtiste()->getImageUrl(),
                ] : null,
                'album' => [
                    'id' => $album->getId(),
                    'titre' => $album->getTitre(),
                    'coverUrl' => $album->getCoverUrl(),
                ],
            ];
        }, $album->getMusiques()->toArray());

        $data = [
            'id' => $album->getId(),
            'titre' => $album->getTitre(),
            'coverUrl' => $album->getCoverUrl(),
            'createdAt' => $album->getCreatedAt()?->format('c'),
            'artiste' => $album->getArtiste() ? [
                'id' => $album->getArtiste()->getId(),
                'nom' => $album->getArtiste()->getNom(),
                'imageUrl' => $album->getArtiste()->getImageUrl(),
            ] : null,
            'musiques' => $musiquesData,
        ];

        return $this->json($data);
    }
}
