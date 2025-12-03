<?php

namespace App\Controller\Web;

use App\Entity\Address;
use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use App\Service\UserProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly UserProfileService $profileService
    ) {
    }

    #[Route('/mon-compte/profil', name: 'app_profile', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $user = $this->resolveViewer($request);
        if (!$user instanceof User) {
            $this->addFlash('info', 'Crée un compte ou connecte-toi pour accéder au profil.');

            return $this->redirectToRoute('app_register');
        }

        $primaryAddress = $this->profileService->guessPrimaryAddress($user)
            ?? (new Address())->setIsDefault(true)->setIsShipping(true)->setIsBilling(true);

        $form = $this->createForm(ProfileType::class, $user, [
            'primary_address' => $primaryAddress,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleAvatarUpload($form->get('avatarFile')->getData(), $user);
            /** @var Address|null $submittedAddress */
            $submittedAddress = $form->get('primaryAddress')->getData();
            $this->profileService->applyProfileUpdates($user, $submittedAddress);
            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('account/profile.html.twig', [
            'profileForm' => $form->createView(),
            'viewer' => $user,
        ]);
    }

    private function resolveViewer(Request $request): ?User
    {
        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User) {
            return $currentUser;
        }

        $recentId = $request->getSession()->get('recent_user_id');
        if ($recentId) {
            return $this->userRepository->find($recentId);
        }

        return null;
    }

    private function handleAvatarUpload(mixed $file, User $user): void
    {
        if (!$file instanceof UploadedFile) {
            return;
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';
        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            $this->addFlash('error', 'Impossible de créer le dossier des avatars.');

            return;
        }

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('avatar-%s.%s', bin2hex(random_bytes(6)), $extension);

        try {
            $file->move($uploadDir, $filename);
            $user->setAvatarPath('uploads/avatars/' . $filename);
        } catch (FileException) {
            $this->addFlash('error', 'Le téléchargement de ton avatar a échoué.');
        }
    }
}
