<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

#[Route('/api/me', name: 'api_me_')]
class MeController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, LoggerInterface $logger): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        $logger->info('MeController called. Auth Header: ' . ($authHeader ? 'Present' : 'Missing'));
        if ($authHeader) {
            $logger->info('Auth Header content: ' . substr($authHeader, 0, 20) . '...');
        }
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        return $this->json([
            'id' => $user->getUserIdentifier(),
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            // 'pseudo' => $user->getPseudo(),
            // 'avatarUrl' => $user->getAvatarUrl(),
        ]);
    }
}
