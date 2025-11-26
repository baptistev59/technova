<?php

namespace App\Install\Step;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Création automatique d'un compte administrateur après installation complète.
 */
class CreateAdminStep implements StepInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function getTitle(): string
    {
        return 'Création du compte administrateur';
    }

    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $helper = new QuestionHelper();

        $output->writeln('<info>→ Vérification de l’existence d’un administrateur...</info>');

        $defaultEmail = "admin@test.com";
        $existing = $this->em->getRepository(User::class)->findOneBy([
            'email' => $defaultEmail // ou 'roles' => ['ROLE_ADMIN']
        ]);

        if ($existing) {
            $output->writeln('<comment>Un administrateur existe déjà. Étape ignorée.</comment>');
            return true;
        }

        // Demander si on souhaite créer l'admin
        $q = new ConfirmationQuestion(
            "Aucun administrateur trouvé. Souhaitez-vous en créer un maintenant ? (Y/n) ",
            true
        );

        if (!$helper->ask($input, $output, $q)) {
            $output->writeln('<comment>→ Création de l’administrateur annulée.</comment>');
            return true;
        }

        // Email
        $emailQ = new Question(
            "Email de l’administrateur (par défaut : admin@test.com) : ",
            "admin@test.com"
        );
        $email = $helper->ask($input, $output, $emailQ);

        // Vérifier si cet email existe déjà
        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existing) {
            $output->writeln('<comment>Un utilisateur avec cet email existe déjà. Étape ignorée.</comment>');
            return true;
        }

        // Mot de passe
        $passQ = new Question(
            'Mot de passe administrateur (par défaut : 123456) : ',
            "123456"
        );
        $passQ->setHidden(true);
        $passQ->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passQ);

        // Création
        try {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname('Admin');
            $user->setLastname('TechNova');
            $user->setRoles(['ROLE_ADMIN']);

            $hash = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hash);

            $this->em->persist($user);
            $this->em->flush();
        } catch (\Exception $e) {
            $output->writeln('<error>Erreur lors de la création de l’administrateur :</error>');
            $output->writeln('<comment>'.$e->getMessage().'</comment>');
            return false;
        }

        $output->writeln('<info>✔ Administrateur créé avec succès.</info>');
        return true;
    }
} 
