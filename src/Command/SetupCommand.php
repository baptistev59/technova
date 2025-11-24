<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:setup',
    description: 'Initialise le projet : migrations + création de l’utilisateur admin',
)]
class SetupCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private DependencyFactory $migrationFactory
    ) {
        parent::__construct();
    }

    /**
     * Méthode exécutée quand on lance :
     *
     *   symfony console app:setup
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("\n== SETUP START ==\n");

        // ----------------------------------------------------------
        // 1. INITIALISATION DU STOCKAGE DES MÉTADONNÉES DE MIGRATIONS
        // ----------------------------------------------------------
        $output->writeln("== SYNC METADATA STORAGE ==");
        // Récupère l’objet responsable de stocker les versions de migration installées
        $storage = $this->migrationFactory->getMetadataStorage();

        // S’assure que la table doctrine_migration_versions existe
        $storage->ensureInitialized();

        $output->writeln("Metadata synced.\n");

        // ----------------------------------------------------------
        // 2. EXÉCUTION DES MIGRATIONS
        // ----------------------------------------------------------
        $output->writeln("== RUNNING MIGRATIONS ==");

        // Resolve "latest" pour savoir jusqu’où appliquer les migrations
        $aliasResolver  = $this->migrationFactory->getVersionAliasResolver();
        $planCalculator = $this->migrationFactory->getMigrationPlanCalculator();

        // Version cible (la dernière migration disponible)
        $latestVersion  = $aliasResolver->resolveVersionAlias('latest');

        // Calcul du plan (migration à exécuter)
        $plan           = $planCalculator->getPlanUntilVersion($latestVersion);

        // Configuration du migrateur
        $config = new MigratorConfiguration();
        $config->setDryRun(false);           // true = simulation, false = réel
        $config->setTimeAllQueries(true);    // chronométrer les requêtes

        // Exécution des migrations
        $migrator = $this->migrationFactory->getMigrator();
        $migrator->migrate($plan, $config);

        $output->writeln("Migrations executed.\n");

        // ----------------------------------------------------------
        // 3. CRÉATION AUTOMATIQUE DE L’UTILISATEUR ADMIN
        // ----------------------------------------------------------
        $output->writeln("== CHECK ADMIN ==");

        // Vérifie si un admin existe déjà
        $existing = $this->em->getRepository(User::class)->findOneBy([
            'email' => 'admin@test.com'
        ]);

        if (!$existing) {
            // Instanciation d’un nouvel utilisateur
            $user = new User();
            $user->setEmail('admin@test.com');
            $user->setFirstname('Admin');
            $user->setLastname('TechNova');
            $user->setRoles(['ROLE_ADMIN']);

            // Hash du mot de passe
            $hashed = $this->passwordHasher->hashPassword($user, "123456");
            $user->setPassword($hashed);

            // Enregistrement dans la base
            $this->em->persist($user);
            $this->em->flush();

            $output->writeln("Admin created.");
        } else {
            $output->writeln("Admin already exists.");
        }

        // ----------------------------------------------------------
        // FIN DU SETUP
        // ----------------------------------------------------------
        $output->writeln("\n== SETUP DONE ==\n");

        return Command::SUCCESS;
    }
}
