<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;


final class MeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(Security $security): JsonResponse
        {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthenticated'], 401);
        }

        return $this->json([
            'id'       => $user->getId(),
            'email'    => $user->getEmail(),
            'roles'    => $user->getRoles(),
        ]);
    }
}
