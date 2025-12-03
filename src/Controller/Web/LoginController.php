<?php

namespace App\Controller\Web;

use App\Form\LoginType;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{email: string, password: string} $data */
            $data = $form->getData();
            $user = $this->userRepository->findOneBy(['email' => strtolower($data['email'])]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                $error = 'Identifiants invalides.';
            } else {
                $token = $this->jwtManager->create($user);
                $session = $request->getSession();
                if ($session) {
                    $session->set('recent_user_id', $user->getId());
                    $session->set('jwt_token', $token);
                }

                $this->addFlash('success', 'Bon retour parmi nous !');

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('security/login.html.twig', [
            'loginForm' => $form->createView(),
            'authError' => $error,
        ]);
    }
}

