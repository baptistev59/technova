<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SetupController extends AbstractController
{
    private DependencyFactory $migrationFactory;

    public function __construct(DependencyFactory $migrationFactory)
    {
        $this->migrationFactory = $migrationFactory;
    }

    #[Route('/run-setup', name: 'run_setup')]
    public function setup(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $output = "== SETUP START ==\n\n";

        // ---- MIGRATIONS ----
        $output .= "== RUNNING MIGRATIONS ==\n\n";

        $command = new MigrateCommand($this->migrationFactory);

        $input = new ArrayInput([
            '--allow-no-migration' => true
        ]);

        $buffer = new BufferedOutput();
        $command->run($input, $buffer);

        $output .= $buffer->fetch();
        $output .= "\nMigrations completed.\n\n";

        // ---- ADMIN USER ----
        $existing = $em->getRepository(User::class)->findOneBy([
            'email' => 'admin@test.com'
        ]);

        if (!$existing) {
            $user = new User();
            $user->setEmail('admin@test.com');
            $user->setFirstname('Admin');
            $user->setLastname('TechNova');
            $user->setRoles(['ROLE_ADMIN']);

            $hashed = $passwordHasher->hashPassword($user, "123456");
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();

            $output .= "Admin user created.\n";
        } else {
            $output .= "Admin already exists.\n";
        }

        return new Response("<pre>$output</pre>");
    }
}
