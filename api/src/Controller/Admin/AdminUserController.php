<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/users', name: 'api_admin_users_')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        // Be careful not to expose password hashes or sensitive info if entity serialization does it.
        // Better to map manually.
        $data = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getUserIdentifier(),
                'pseudo' => $user->getPseudo(),
                'roles' => $user->getRoles(),
                'avatarUrl' => $user->getAvatarUrl(),
                'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }, $users);

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $passwordHasher,
        ImageUploader $imageUploader
    ): JsonResponse {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $pseudo = $request->request->get('pseudo');
        $roles = $request->request->get('roles'); // Expecting json array or array
        $avatarFile = $request->files->get('avatar');

        if (!$email || !$password) {
            return $this->json(['message' => 'Email and Password required'], 400);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPseudo($pseudo ?? substr($email, 0, strpos($email, '@')));
        
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        if ($roles) {
             if (is_string($roles)) $roles = json_decode($roles, true);
             if (is_array($roles)) $user->setRoles($roles);
        }

        if ($avatarFile) {
            $path = $imageUploader->upload($avatarFile, 'avatars');
            $user->setAvatarUrl($path);
        }
        
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setModifiedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'message' => 'User created'
        ], 201);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'roles' => $user->getRoles(),
            'avatarUrl' => $user->getAvatarUrl(),
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['POST', 'PUT', 'PATCH'])]
    public function update(
        User $user, 
        Request $request, 
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ImageUploader $imageUploader
    ): JsonResponse {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $pseudo = $request->request->get('pseudo');
        $roles = $request->request->get('roles');
        $avatarFile = $request->files->get('avatar');
        
        if ($request->getContentTypeFormat() === 'json') {
             $data = json_decode($request->getContent(), true);
             $email = $data['email'] ?? $email;
             $password = $data['password'] ?? $password;
             $pseudo = $data['pseudo'] ?? $pseudo;
             $roles = $data['roles'] ?? $roles;
        }

        if ($email) $user->setEmail($email);
        if ($pseudo) $user->setPseudo($pseudo);
        
        if ($password) {
             $hashedPassword = $passwordHasher->hashPassword($user, $password);
             $user->setPassword($hashedPassword);
        }

         if ($roles) {
             if (is_string($roles)) $roles = json_decode($roles, true);
             if (is_array($roles)) $user->setRoles($roles);
        }

        if ($avatarFile) {
            $path = $imageUploader->upload($avatarFile, 'avatars');
            $user->setAvatarUrl($path);
        }
        
        $user->setModifiedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'message' => 'User updated'
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'User deleted']);
    }
}
