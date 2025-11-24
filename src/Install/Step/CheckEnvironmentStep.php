<?php

namespace App\Install\Step;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Vérifie l'environnement PHP
 */
class CheckEnvironmentStep implements StepInterface
{
    public function getTitle(): string
    {
        return 'Vérification de l’environnement PHP';
    }

    // Vérifie l’environnement PHP
    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $output->writeln('<info>→ Vérification de l’environnement...</info>');

        $ok = true;

        // Vérif version PHP
        if (PHP_VERSION_ID < 80200) {
            $output->writeln('<error>PHP 8.2 minimum requis.</error>');
            $ok = false;
        } else {
            $output->writeln('<comment>PHP version : '.PHP_VERSION.'</comment>');
        }

        // Vérif extension pdo_pgsql
        if (!extension_loaded('pdo_pgsql')) {
            $output->writeln('<error>Extension pdo_pgsql manquante. Activez-la dans votre php.ini.</error>');
            $ok = false;
        } else {
            $output->writeln('<comment>Extension pdo_pgsql OK</comment>');
        }

        // On pourrait ajouter d’autres vérifs : openssl, mbstring, etc.

        if ($ok) {
            $output->writeln('<info>✔ Environnement OK</info>');
        }

        return $ok;
    }
}
