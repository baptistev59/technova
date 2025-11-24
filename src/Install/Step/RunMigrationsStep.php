<?php

namespace App\Install\Step;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exécution des migrations
 */
class RunMigrationsStep implements StepInterface
{
    public function getTitle(): string
    {
        return 'Exécution des migrations Doctrine';
    }

    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln('<info>→ Exécution des migrations...</info>');

        // On lance une nouvelle commande symfony pour utiliser la nouvelle config .env.local
        $cmd = 'symfony console doctrine:migrations:migrate --no-interaction';

        // On exécuter la commande
        $exitCode = 0;
        $output->writeln('<comment>'.$cmd.'</comment>');
        passthru($cmd, $exitCode);

        // Si l'exécuton des migrations a echoué
        if ($exitCode !== 0) {
            $output->writeln('<error>❌ Les migrations ont échoué.</error>');
            $output->writeln('<comment>Si le message parle de colonne manquante (ex: avatar),</comment>');
            $output->writeln('<comment>pensez à exécuter : symfony console make:migration</comment>');
            return false;
        }

        $output->writeln('<info>✔ Migrations exécutées avec succès</info>');
        return true;
    }
}
