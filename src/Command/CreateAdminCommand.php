<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur',
)]

class CreateAdminCommand extends Command
{
    
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = "admin@test.com";
        $password = "123456";

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existing) {
            $output->writeln("<comment>L'utilisateur existe déjà.</comment>");
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstname("Admin");
        $user->setLastname("TechNova");
        $user->setRoles(["ROLE_ADMIN"]);

        $hash = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hash);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln("<info>Utilisateur admin créé avec succès.</info>");
        return Command::SUCCESS;
    }
}
