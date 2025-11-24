<?php

namespace App\Install\Step;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Résumé final du setup
 */
class SummaryStep implements StepInterface
{
    public function getTitle(): string
    {
        return 'Résumé de l’installation';
    }

    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        // Affichage du résumé
        $output->writeln('');
        $output->writeln('<info>🎉 INSTALLATION TERMINÉE 🎉</info>');
        $output->writeln('');
        $output->writeln('<comment>- Environnement vérifié</comment>');
        $output->writeln('<comment>- Base de données configurée</comment>');
        $output->writeln('<comment>- Connexion PostgreSQL testée</comment>');
        $output->writeln('<comment>- Migrations exécutées</comment>');
        $output->writeln('');
        $output->writeln('<info>Vous pouvez maintenant lancer le back-office et créer vos premiers utilisateurs.</info>');
        $output->writeln('');

        return true;
    }
}
