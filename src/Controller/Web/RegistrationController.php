<?php

namespace App\Controller\Web;

use App\Form\RegistrationType;
use App\Service\UserRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(private readonly UserRegistrationService $registrationService)
    {
    }

    #[Route('/inscription', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);

        $errors = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $payload = $form->getData();
            $result = $this->registrationService->register($payload);

            if (($result['status'] ?? Response::HTTP_INTERNAL_SERVER_ERROR) === Response::HTTP_CREATED) {
                $this->addFlash('success', 'Bienvenue sur TechNova ! Le profil est prêt à être complété.');
                if (isset($result['data']['user']['id'])) {
                    $request->getSession()->set('recent_user_id', $result['data']['user']['id']);
                }

                return $this->redirectToRoute('app_profile');
            }

            $errors = implode(' ', $result['errors'] ?? ['Une erreur est survenue.']);
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
            'apiError' => $errors,
        ]);
    }
}
