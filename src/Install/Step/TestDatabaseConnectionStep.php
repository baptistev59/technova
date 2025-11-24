<?php

namespace App\Install\Step;

use App\Install\Util\EnvReader;
use App\Install\Util\DatabaseDsnParser;
use PDO;
use PDOException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test la connexion à la BDD
 */
class TestDatabaseConnectionStep implements StepInterface
{
    public function __construct(
        private EnvReader $envReader,
        private DatabaseDsnParser $dsnParser,
    ) {}

    public function getTitle(): string
    {
        return 'Test de connexion à la base de données';
    }

    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln('<info>→ Test de connexion à PostgreSQL...</info>');

        // Récupérer la configuration actuelle
        $dsn = $this->envReader->readDatabaseUrl();
        if (!$dsn) {
            $output->writeln('<error>Aucune DATABASE_URL trouvée dans .env.local.</error>');
            return false;
        }

        // Test de connexion
        $parts = $this->dsnParser->parse($dsn);

        // Construire la DSN
        $pdoDsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $parts['host'],
            $parts['port'],
            $parts['db']
        );

        try {
            // Créer une connexion
            $pdo = new PDO($pdoDsn, $parts['user'], $parts['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Faire une requête de test
            $pdo->query('SELECT 1');
            $output->writeln('<info>✔ Connexion PostgreSQL OK</info>');
            return true;
        } catch (PDOException $e) {
            $msg = $e->getMessage();

            // Afficher le message d'erreur
            if (str_contains($msg, 'does not exist')) {
                $output->writeln('<error>❌ La base "'.$parts['db'].'" n’existe pas.</error>');
                $output->writeln('<comment>Si vous êtes sur un hébergement mutualisé (AlwaysData, OVH…),</comment>');
                $output->writeln('<comment>créez la base manuellement dans le panneau d’administration, puis relancez l’installation.</comment>');
            } elseif (str_contains($msg, 'password authentication')) {
                $output->writeln('<error>❌ Échec d’authentification PostgreSQL.</error>');
                $output->writeln('<comment>Vérifiez l’utilisateur/mot de passe dans .env.local.</comment>');
            } else {
                $output->writeln('<error>❌ Impossible de se connecter à PostgreSQL :</error>');
                $output->writeln('<comment>'.$msg.'</comment>');
            }

            return false;
        }
    }
}
