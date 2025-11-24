<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserAvatarController extends AbstractController
{
    #[Route('/user/avatar', name: 'app_user_avatar')]
    public function index(): Response
    {
        return $this->render('user_avatar/index.html.twig', [
            'controller_name' => 'UserAvatarController',
        ]);
    }
}
