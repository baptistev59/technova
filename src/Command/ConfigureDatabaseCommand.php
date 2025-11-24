<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:configure-database',
    description: 'Assistant interactif pour configurer DATABASE_URL dans .env.local'
)]
class ConfigureDatabaseCommand extends Command
{
    /**
     * Méthode principale exécutée lorsque la commande est lancée.
     * C’est ici que l’on affiche les questions, construit l’URL PostgreSQL
     * et écrit automatiquement la configuration dans .env.local
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Helper permettant de poser des questions interactives dans la CLI
        $helper = $this->getHelper('question');

        // Titre affiché dans le terminal
        $output->writeln("\n<info>=== CONFIGURATION DE LA BASE DE DONNÉES ===</info>\n");

        // --------------------------------------------------------
        // QUESTIONS INTERACTIVES
        // --------------------------------------------------------
        // Chaque question est un objet Question avec une valeur par défaut éventuelle

        $hostQ = new Question('Hôte PostgreSQL (ex: localhost) : ', 'localhost');
        $portQ = new Question('Port PostgreSQL (ex: 5432) : ', '5432');

        // Pas de valeur par défaut pour la base → l’utilisateur doit la fournir
        $dbQ   = new Question('Nom de la base : ');

        $userQ = new Question("Nom d'utilisateur PostgreSQL : ");

        // Pour le mot de passe : masqué et sécurisé
        $passQ = new Question('Mot de passe PostgreSQL : ');
        $passQ->setHidden(true);
        $passQ->setHiddenFallback(false);

        // Pose des questions et enregistre les réponses
        $host = $helper->ask($input, $output, $hostQ);
        $port = $helper->ask($input, $output, $portQ);
        $db   = $helper->ask($input, $output, $dbQ);
        $user = $helper->ask($input, $output, $userQ);
        $pass = $helper->ask($input, $output, $passQ);

        // --------------------------------------------------------
        // CONSTRUCTION DE L’URL DATABASE_URL
        // --------------------------------------------------------
        // Format officiel pour PostgreSQL dans Symfony
        // Exemple :
        //   postgresql://user:password@host:5432/dbname?serverVersion=16&charset=utf8

        $databaseUrl = sprintf(
            'postgresql://%s:%s@%s:%s/%s?serverVersion=16&charset=utf8',
            $user,
            $pass,
            $host,
            $port,
            $db
        );

        // --------------------------------------------------------
        // ÉCRITURE AUTOMATIQUE DANS .env.local
        // --------------------------------------------------------
        // Ce fichier remplace les valeurs de .env uniquement en local
        // et ne doit jamais être poussé en production.
        // On utilise Filesystem() pour écrire de manière sécurisée.

        $envPath = dirname(__DIR__, 2) . '/.env.local';
        $fs = new Filesystem();

        // On remplace entièrement le contenu de .env.local par la variable DATABASE_URL
        $fs->dumpFile($envPath, "DATABASE_URL=\"$databaseUrl\"\n");

        // Message de confirmation
        $output->writeln("\n<info>.env.local mis à jour avec succès !</info>");
        $output->writeln("DATABASE_URL = $databaseUrl\n");

        // --------------------------------------------------------
        // PROPOSER D’ENCHAÎNER AVEC app:setup
        // --------------------------------------------------------
        // Cette commande permet d’exécuter les migrations et créer l’admin automatiquement.

        $output->writeln("<comment>Voulez-vous lancer app:setup maintenant ? (y/N)</comment>");
        $answer = strtolower(trim(fgets(STDIN)));

        // Si l’utilisateur répond 'y', on appelle immédiatement app:setup
        if ($answer === 'y') {
            $output->writeln("\n<info>Exécution de app:setup...</info>\n");
            passthru('symfony console app:setup');
        }

        // Fin de la commande
        $output->writeln("\n<info>Configuration terminée.</info>\n");

        return Command::SUCCESS;
    }
}
