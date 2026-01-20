<?php

namespace App\Controller\Admin;

use App\Entity\Artiste;
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

#[Route('/api/admin/artistes', name: 'api_admin_artistes_')]
#[IsGranted('ROLE_ADMIN')]
class AdminArtisteController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ArtisteRepository $artisteRepository): JsonResponse
    {
        $artistes = $artisteRepository->findAll();

        $data = array_map(function (Artiste $artiste) {
            return [
                'id' => $artiste->getId(),
                'nom' => $artiste->getNom(),
                'imageUrl' => $artiste->getImageUrl(),
                'createdAt' => $artiste->getCreatedAt()?->format('c'),
                'modifiedAt' => $artiste->getModifiedAt()?->format('c'),
            ];
        }, $artistes);

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ImageUploader $imageUploader): JsonResponse
    {
        $nom = $request->request->get('nom');
        $imageFile = $request->files->get('image');

        if (!$nom) {
            return $this->json(['message' => 'Nom required'], 400);
        }

        $artiste = new Artiste();
        $artiste->setNom($nom);

        if ($imageFile) {
            $path = $imageUploader->upload($imageFile, 'artistes');
            $artiste->setImageUrl($path);
        }

        $em->persist($artiste);
        $em->flush();

        $data = [
            'id' => $artiste->getId(),
            'nom' => $artiste->getNom(),
            'imageUrl' => $artiste->getImageUrl(),
            'createdAt' => $artiste->getCreatedAt()?->format('c'),
            'modifiedAt' => $artiste->getModifiedAt()?->format('c'),
        ];

        return $this->json($data, 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Artiste $artiste): JsonResponse
    {
        $data = [
            'id' => $artiste->getId(),
            'nom' => $artiste->getNom(),
            'imageUrl' => $artiste->getImageUrl(),
            'createdAt' => $artiste->getCreatedAt()?->format('c'),
            'modifiedAt' => $artiste->getModifiedAt()?->format('c'),
        ];

        return $this->json($data);
    }

    #[Route('/{id}', name: 'update', methods: ['POST', 'PUT', 'PATCH'])]
    public function update(Artiste $artiste, Request $request, EntityManagerInterface $em, ImageUploader $imageUploader): JsonResponse
    {
        // Handle POST form-data for files, or JSON if no files
        // Symfony handles form-data in $request->request and $request->files

        // If content-type is json, we need to decode manually, but for file upload we must use multipart/form-data
        // For simplicity i'll assume multipart/form-data for updates with files, or check content type

        $nom = $request->request->get('nom');
        $imageFile = $request->files->get('image');

        // If it's a JSON request (no file update)
        if ($request->getContentTypeFormat() === 'json') {
            $data = json_decode($request->getContent(), true);
            $nom = $data['nom'] ?? $nom;
        }

        if ($nom) {
            $artiste->setNom($nom);
        }

        if ($imageFile) {
            $path = $imageUploader->upload($imageFile, 'artistes');
            $artiste->setImageUrl($path);
        }

        $em->flush();

        return $this->json($artiste);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Artiste $artiste, EntityManagerInterface $em, MusiqueRepository $musiqueRepository, AlbumRepository $albumRepository): JsonResponse
    {
        try {

            $albums = $albumRepository->findBy(['artiste' => $artiste]);

            foreach ($albums as $album) {
                $musiques = $musiqueRepository->findBy(['album' => $album]);
                foreach ($musiques as $musique) {
                    $em->remove($musique);
                }   
                $em->remove($album);
            }

            $em->remove($artiste);
            $em->flush();

            return $this->json(['message' => 'Artiste deleted']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error deleting artiste: ' . $e->getMessage()], 400);
        }
    }
}
