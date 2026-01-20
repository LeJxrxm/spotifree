<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setPseudo($data['pseudo'] ?? '');
        
        $plainPassword = $data['password'] ?? '';
        
        // Basic validation before hashing (more complex validation should be done with constraints on Entity or DTO)
        if (empty($plainPassword)) {
             return $this->json(['message' => 'Password is required'], 400);
        }

        // Hash the password
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        
        // Default roles
        $user->setRoles(['ROLE_USER']);

        // Validate entity
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['message' => 'Validation failed', 'errors' => $errorMessages], 400);
        }

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $e) {
            // Handle unique constraint violation (e.g. email already exists)
            // Ideally check specifically for unique constraint violation exception
            return $this->json(['message' => 'User could not be created', 'error' => $e->getMessage()], 400);
        }

        return $this->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'pseudo' => $user->getPseudo()
            ]
        ], 201);
    }
}
