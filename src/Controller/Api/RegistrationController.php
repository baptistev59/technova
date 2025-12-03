<?php

namespace App\Controller\Api;

use App\Service\UserRegistrationService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/register')]
class RegistrationController extends AbstractController
{
    public function __construct(private readonly UserRegistrationService $registrationService)
    {
    }

    #[Route('', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        summary: 'Inscription d’un client',
        description: 'Crée un compte client et renvoie directement un JWT utilisable.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'firstname', 'lastname'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'client@test.fr'),
                    new OA\Property(property: 'password', type: 'string', example: 'P@ssword123'),
                    new OA\Property(property: 'firstname', type: 'string', example: 'Alex'),
                    new OA\Property(property: 'lastname', type: 'string', example: 'Martin'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Compte créé + JWT retourné',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'email', type: 'string', format: 'email'),
                                new OA\Property(property: 'firstname', type: 'string'),
                                new OA\Property(property: 'lastname', type: 'string'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Payload invalide'),
            new OA\Response(response: 409, description: 'Email déjà utilisé'),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];

        $result = $this->registrationService->register($payload);

        if ($result['status'] !== Response::HTTP_CREATED) {
            return $this->json(
                $result['errors'] ?? ['error' => 'Requête invalide'],
                $result['status']
            );
        }

        return $this->json($result['data'], Response::HTTP_CREATED);
    }
}
