<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SetupController extends AbstractController
{
    #[Route('/run-setup', name: 'run_setup')]
    public function setup(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        #[Autowire(service: 'doctrine.migrations.dependency_factory')]
        DependencyFactory $migrationFactory
    ): Response {
        $output = "== SETUP START ==\n\n";

        try {
            // ----------------------------------------------------------
            // SYNC METADATA STORAGE
            // ----------------------------------------------------------
            $output .= "== SYNC METADATA STORAGE ==\n";

            $storage = $migrationFactory->getMetadataStorage();
            $storage->ensureInitialized();
            $output .= "Metadata storage synced.\n\n";

            // ----------------------------------------------------------
            // EXÉCUTER LES MIGRATIONS
            // ----------------------------------------------------------
            $output .= "== RUNNING MIGRATIONS ==\n";

            $aliasResolver   = $migrationFactory->getVersionAliasResolver();
            $planCalculator  = $migrationFactory->getMigrationPlanCalculator();
            $latestVersion   = $aliasResolver->resolveVersionAlias('latest');
            $plan            = $planCalculator->getPlanUntilVersion($latestVersion);

            $config = new MigratorConfiguration();
            $config->setDryRun(false);
            $config->setTimeAllQueries(true);

            $migrator = $migrationFactory->getMigrator();
            $migrator->migrate($plan, $config);

            $output .= "Migrations executed successfully.\n\n";

        } catch (\Throwable $e) {
            return new Response(
                "<pre>Migration error:\n" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>",
                500
            );
        }

        // ----------------------------------------------------------
        // CRÉER L’UTILISATEUR ADMIN SI NON EXISTANT
        // ----------------------------------------------------------
        $output .= "== CREATING ADMIN USER ==\n";

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
            $output .= "Admin user already exists.\n";
        }

        return new Response("<pre>$output\n\n== SETUP DONE ==</pre>");
    }
}
