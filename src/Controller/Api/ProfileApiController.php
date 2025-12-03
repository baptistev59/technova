<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\UserProfileService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Route('/api/profile')]
#[OA\Tag(name: 'Profile')]
class ProfileApiController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly UserProfileService $profileService
    ) {
    }

    #[Route('', name: 'api_profile_get', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Get(
        summary: 'Récupération du profil utilisateur',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil complet',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'firstname', type: 'string'),
                        new OA\Property(property: 'lastname', type: 'string'),
                        new OA\Property(property: 'phone', type: 'string', nullable: true),
                        new OA\Property(property: 'avatarPath', type: 'string', nullable: true),
                        new OA\Property(property: 'newsletterOptIn', type: 'boolean'),
                        new OA\Property(
                            property: 'address',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'label', type: 'string', nullable: true),
                                new OA\Property(property: 'addressLine1', type: 'string', nullable: true),
                                new OA\Property(property: 'addressLine2', type: 'string', nullable: true),
                                new OA\Property(property: 'postalCode', type: 'string', nullable: true),
                                new OA\Property(property: 'city', type: 'string', nullable: true),
                                new OA\Property(property: 'state', type: 'string', nullable: true),
                                new OA\Property(property: 'country', type: 'string', nullable: true),
                                new OA\Property(property: 'isDefault', type: 'boolean'),
                                new OA\Property(property: 'isShipping', type: 'boolean'),
                                new OA\Property(property: 'isBilling', type: 'boolean'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function show(): JsonResponse
    {
        $user = $this->getViewer();

        return $this->json($this->profileService->profileToArray($user));
    }

    #[Route('', name: 'api_profile_update', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Post(
        summary: 'Mise à jour du profil utilisateur',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstname', type: 'string', nullable: true),
                    new OA\Property(property: 'lastname', type: 'string', nullable: true),
                    new OA\Property(property: 'phone', type: 'string', nullable: true),
                    new OA\Property(property: 'avatarPath', type: 'string', nullable: true),
                    new OA\Property(property: 'newsletterOptIn', type: 'boolean', nullable: true),
                    new OA\Property(
                        property: 'address',
                        type: 'object',
                        nullable: true,
                        properties: [
                            new OA\Property(property: 'label', type: 'string', nullable: true),
                            new OA\Property(property: 'addressLine1', type: 'string', nullable: true),
                            new OA\Property(property: 'addressLine2', type: 'string', nullable: true),
                            new OA\Property(property: 'postalCode', type: 'string', nullable: true),
                            new OA\Property(property: 'city', type: 'string', nullable: true),
                            new OA\Property(property: 'state', type: 'string', nullable: true),
                            new OA\Property(property: 'country', type: 'string', nullable: true),
                            new OA\Property(property: 'isDefault', type: 'boolean', nullable: true),
                            new OA\Property(property: 'isShipping', type: 'boolean', nullable: true),
                            new OA\Property(property: 'isBilling', type: 'boolean', nullable: true),
                        ]
                    )
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profil mis à jour avec succès'),
            new OA\Response(response: 400, description: 'Payload invalide'),
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $user = $this->getViewer();

        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            throw new BadRequestHttpException('JSON invalide ou vide.');
        }

        if (array_key_exists('firstname', $payload)) {
            $user->setFirstname((string) $payload['firstname']);
        }
        if (array_key_exists('lastname', $payload)) {
            $user->setLastname((string) $payload['lastname']);
        }
        if (array_key_exists('phone', $payload)) {
            $user->setPhone($payload['phone'] ? (string) $payload['phone'] : null);
        }
        if (array_key_exists('avatarPath', $payload)) {
            $user->setAvatarPath($payload['avatarPath'] ?: null);
        }
        if (array_key_exists('newsletterOptIn', $payload)) {
            $user->setNewsletterOptIn((bool) $payload['newsletterOptIn']);
        }

        $addressData = $payload['address'] ?? null;
        $address = $this->profileService->addressFromArray(is_array($addressData) ? $addressData : null);
        $this->profileService->applyProfileUpdates($user, $address);

        return $this->json($this->profileService->profileToArray($user), Response::HTTP_OK);
    }

    private function getViewer(): User
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Authentification requise.');
        }

        return $user;
    }
}
