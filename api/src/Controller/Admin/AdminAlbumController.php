<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use App\Repository\ArtisteRepository;
use App\Repository\MusiqueRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/albums', name: 'api_admin_albums_')]
#[IsGranted('ROLE_ADMIN')]
class AdminAlbumController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): JsonResponse
    {
        $albums = $albumRepository->findAll();
        
        $data = array_map(function(Album $album) {
            $artiste = $album->getArtiste();
            return [
                'id' => $album->getId(),
                'titre' => $album->getTitre(),
                'coverUrl' => $album->getCoverUrl(),
                'createdAt' => $album->getCreatedAt()?->format('c'),
                'modifiedAt' => $album->getModifiedAt()?->format('c'),
                'artiste' => $artiste ? [
                    'id' => $artiste->getId(),
                    'nom' => $artiste->getNom(),
                ] : null,
            ];
        }, $albums);
        
        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ImageUploader $imageUploader, ArtisteRepository $artisteRepository): JsonResponse
    {
        $titre = $request->request->get('titre');
        $artisteId = $request->request->get('artiste');
        $coverFile = $request->files->get('cover');

        if (!$titre || !$artisteId) {
            return $this->json(['message' => 'Titre and Artiste required'], 400);
        }

        $artiste = $artisteRepository->find($artisteId);
        if (!$artiste) {
             return $this->json(['message' => 'Artiste not found'], 404);
        }

        $album = new Album();
        $album->setTitre($titre);
        $album->setArtiste($artiste);

        if ($coverFile) {
            $path = $imageUploader->upload($coverFile, 'albums');
            $album->setCoverUrl($path);
        }

        $em->persist($album);
        $em->flush();

        return $this->json($album, 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Album $album): JsonResponse
    {
        $artiste = $album->getArtiste();
        $data = [
            'id' => $album->getId(),
            'titre' => $album->getTitre(),
            'coverUrl' => $album->getCoverUrl(),
            'createdAt' => $album->getCreatedAt()?->format('c'),
            'modifiedAt' => $album->getModifiedAt()?->format('c'),
            'artiste' => $artiste ? [
                'id' => $artiste->getId(),
                'nom' => $artiste->getNom(),
            ] : null,
        ];
        
        return $this->json($data);
    }

    #[Route('/{id}', name: 'update', methods: ['POST', 'PUT', 'PATCH'])]
    public function update(Album $album, Request $request, EntityManagerInterface $em, ImageUploader $imageUploader, ArtisteRepository $artisteRepository): JsonResponse
    {
        $titre = $request->request->get('titre');
        $artisteId = $request->request->get('artiste');
        $coverFile = $request->files->get('cover');

        if ($request->getContentTypeFormat() === 'json') {
             $data = json_decode($request->getContent(), true);
             $titre = $data['titre'] ?? $titre;
             $artisteId = $data['artiste'] ?? $artisteId;
        }

        if ($titre) {
            $album->setTitre($titre);
        }
        
        if ($artisteId) {
             $artiste = $artisteRepository->find($artisteId);
             if ($artiste) {
                 $album->setArtiste($artiste);
             }
        }

        if ($coverFile) {
            $path = $imageUploader->upload($coverFile, 'albums');
            $album->setCoverUrl($path);
        }

        $em->flush();

        return $this->json($album);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Album $album, EntityManagerInterface $em, MusiqueRepository $musiqueRepository): JsonResponse
    {
        $musiques = $musiqueRepository->findBy(['album' => $album]);
        foreach ($musiques as $musique) {
            $em->remove($musique);
        }
        $em->remove($album);
        $em->flush();

        return $this->json(['message' => 'Album deleted']);
    }
}
