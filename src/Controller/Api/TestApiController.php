<?php

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Endpoint de healthcheck très simple utilisé pendant la formation ou par Postman.
 */
class TestApiController extends AbstractController
{
    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    #[OA\Get(
        path: '/api/test',
        summary: 'Ping de disponibilité',
        description: 'Retourne un message lorsque l’API répond correctement.',
        tags: ['System'],
        security: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API opérationnelle',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'TechNova API répond bien 🚀'),
                    ]
                )
            )
        ]
    )]
    public function index(LoggerInterface $technovaLogger): JsonResponse
    {
        // Permet de vérifier dans les logs qu'un appel a bien été traité
        $technovaLogger->info('Appel réussi sur /api/test depuis React');

        return $this->json([
            'status' => 'ok',
            'message' => 'TechNova API répond bien 🚀',
        ]);
    }
}
