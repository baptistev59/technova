<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;

/**
 * @Route("/api/test", methods={"GET"})
 * @OA\Get(
 *     summary="Tester si l’API fonctionne",
 *     description="Retourne un message pour API status check.",
 *     @OA\Response(
 *         response=200,
 *         description="Réponse OK",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="ok"),
 *             @OA\Property(property="message", type="string", example="TechNova API répond bien 🚀")
 *         )
 *     )
 * )
 * @OA\Tag(name="System")
 */
class TestApiController extends AbstractController
{   #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    #[OA\Get(
        path: '/api/test',
        summary: 'Vérifie si l’API TechNova fonctionne',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API opérationnelle',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            )
        ]
    )]
    public function index(LoggerInterface $technovaLogger): JsonResponse
    {
        // Écriture dans le canal "technova"
        $technovaLogger->info('Appel réussi sur /api/test depuis React');

        return $this->json([
            'status' => 'ok',
            'message' => 'TechNova API répond bien 🚀',
        ]);
    }
}