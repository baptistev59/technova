<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Route factice utilisée par le firewall json_login.
 * L'exécution ne devrait jamais atteindre ce contrôleur : si c'est le cas,
 * c'est que la configuration de sécurité n'intercepte pas /api/login.
 */
class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
        throw new \LogicException('Handled by Symfony security json_login firewall.');
    }
}
