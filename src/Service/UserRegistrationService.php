<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array{
     *     status: int,
     *     data?: array{token: string, user: array{id: int, email: string, firstname: string, lastname: string}},
     *     errors?: array<string, string>
     * }
     */
    public function register(array $payload): array
    {
        $errors = $this->validatePayload($payload);

        if (!empty($errors)) {
            return [
                'status' => Response::HTTP_BAD_REQUEST,
                'errors' => $errors,
            ];
        }

        $email = strtolower(trim($payload['email']));

        if ($this->userRepository->findOneBy(['email' => $email])) {
            return [
                'status' => Response::HTTP_CONFLICT,
                'errors' => ['email' => 'Un compte utilise déjà cet email'],
            ];
        }

        $user = (new User())
            ->setEmail($email)
            ->setFirstname(trim($payload['firstname']))
            ->setLastname(trim($payload['lastname']))
            ->setRoles([]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $payload['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $this->jwtManager->create($user);

        return [
            'status' => Response::HTTP_CREATED,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                ],
            ],
        ];
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array<string, string>
     */
    private function validatePayload(array $payload): array
    {
        $errors = [];

        $email = strtolower(trim($payload['email'] ?? ''));
        if ('' === $email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide';
        }

        $password = $payload['password'] ?? '';
        if (strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ('' === trim($payload['firstname'] ?? '')) {
            $errors['firstname'] = 'Le prénom est obligatoire.';
        }

        if ('' === trim($payload['lastname'] ?? '')) {
            $errors['lastname'] = 'Le nom est obligatoire.';
        }

        return $errors;
    }
}

