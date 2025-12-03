<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountMenuController extends AbstractController
{
    #[Route('/deconnexion', name: 'app_logout_web', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        if ($session) {
            $session->remove('recent_user_id');
            $session->migrate(true);
        }

        $this->addFlash('success', 'À très vite sur TechNova !');

        return $this->redirectToRoute('homepage');
    }
}

