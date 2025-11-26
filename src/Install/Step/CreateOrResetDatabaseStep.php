<?php

namespace App\Install\Step;

use App\Install\Util\EnvReader;
use App\Install\Util\DatabaseDsnParser;
use PDO;
use PDOException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Création ou suppression de la base si nécessaire
 */
class CreateOrResetDatabaseStep implements StepInterface
{
    public function __construct(
        private EnvReader $envReader,
        private DatabaseDsnParser $dsnParser,
    ) {}

    public function getTitle(): string
    {
        return 'Création / réinitialisation de la base de données';
    }

    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $helper = new QuestionHelper();

        $output->writeln('<info>→ Vérification de l’existence de la base de données...</info>');

        // Lecture du DSN actuel
        $dsn = $this->envReader->readDatabaseUrl();
        if (!$dsn) {
            $output->writeln('<error>Aucune DATABASE_URL détectée.</error>');
            return false;
        }

        $parts = $this->dsnParser->parse($dsn);
        $dbName = $parts['db'];
        $user   = $parts['user'];
        $pass   = $parts['pass'];
        $host   = $parts['host'];
        $port   = $parts['port'];

        // Connexion au postgres "administratif"
        $adminDsn = "pgsql:host=$host;port=$port;dbname=postgres";

        try {
            $adminPdo = new PDO($adminDsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            $output->writeln('<error>⚠ Impossible d’accéder au serveur PostgreSQL : </error>');
            $output->writeln('<comment>'.$e->getMessage().'</comment>');
            return false;
        }

        // Vérifier si la base existe déjà
        try {
            $stmt = $adminPdo->prepare("SELECT 1 FROM pg_database WHERE datname = :db");
            $stmt->execute(['db' => $dbName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $output->writeln('<error>⚠ Impossible de vérifier l’existence de la base : </error>');
            $output->writeln('<comment>'.$e->getMessage().'</comment>');
            return false;
        }

        if ($exists) {
            // La base existe → proposer RESET
            $output->writeln("<comment>La base <info>$dbName</info> existe déjà.</comment>");

            $confirmDrop = new ConfirmationQuestion(
                "Souhaitez-vous la SUPPRIMER puis la recréer ? (Y/n) ",
                false
            );

            if (!$helper->ask($input, $output, $confirmDrop)) {
                $output->writeln('<comment>→ Base conservée.</comment>');
                return true;
            }

            // Confirmation finale
            $doubleCheck = new ConfirmationQuestion(
                "⚠ CONFIRMATION FINALE : la base \"$dbName\" sera SUPPRIMÉE. Continuer ? (Y/n) ",
                false
            );

            if (!$helper->ask($input, $output, $doubleCheck)) {
                $output->writeln('<comment>→ Suppression annulée.</comment>');
                return true;
            }

            // Supprimer la base
            try {
                $adminPdo->exec("DROP DATABASE \"$dbName\"");
                $output->writeln('<info>✔ Base supprimée.</info>');
            } catch (PDOException $e) {
                $output->writeln('<error>Erreur lors de la suppression :</error>');
                $output->writeln('<comment>'.$e->getMessage().'</comment>');
                return false;
            }

            // Recréation
            try {
                $adminPdo->exec("CREATE DATABASE \"$dbName\"");
                $output->writeln('<info>✔ Base recréée avec succès.</info>');
                return true;
            } catch (PDOException $e) {
                $output->writeln('<error>Erreur lors de la création :</error>');
                $output->writeln('<comment>'.$e->getMessage().'</comment>');
                return false;
            }
        }

        // Base inexistante → proposer de la créer
        $output->writeln("<info>La base \"$dbName\" n’existe pas.</info>");

        $createQuestion = new ConfirmationQuestion(
            "Souhaitez-vous la créer ? (Y/n) ",
            true
        );

        if (!$helper->ask($input, $output, $createQuestion)) {
            $output->writeln('<comment>→ Création annulée.</comment>');
            return false;
        }

        // Créer la base
        try {
            $adminPdo->exec("CREATE DATABASE \"$dbName\"");
            $output->writeln('<info>✔ Base créée avec succès.</info>');
            return true;
        } catch (PDOException $e) {
            $output->writeln('<error>Erreur lors de la création :</error>');
            $output->writeln('<comment>'.$e->getMessage().'</comment>');
            return false;
        }
    }
}
