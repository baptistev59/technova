<?php

namespace App\Install\Step;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Réparation automatique des migrations Doctrine si elles sont incohérentes
 */
class RepairMigrationsStep implements StepInterface
{
    public function getTitle(): string
    {
        return 'Vérification / réparation des migrations Doctrine';
    }

    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        $helper = new QuestionHelper();

        $output->writeln('<info>→ Vérification de la cohérence des migrations...</info>');

        // TEST DRY-RUN
        $process = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--dry-run']);
        $process->run();

        if ($process->isSuccessful()) {
            $output->writeln('<info>✔ Les migrations sont cohérentes.</info>');
            return true;
        }

        // Si on est ici → migrations KO
        $output->writeln('<error>❌ Les migrations semblent incohérentes.</error>');
        $output->writeln('<comment>'.$process->getErrorOutput().'</comment>');

        $question = new ConfirmationQuestion(
            "\n⚠ Souhaitez-vous SUPPRIMER toutes les migrations et en générer de nouvelles ? (Y/n) ",
            true
        );

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<comment>→ Réparation annulée. Les migrations restent inchangées.</comment>');
            return false; // On force l’arrêt du wizard
        }

        // SUPPRESSION des migrations
        $output->writeln('<info>→ Suppression des migrations existantes...</info>');
        $migrationsPath = __DIR__.'/../../../migrations';

        if (is_dir($migrationsPath)) {
            foreach (glob($migrationsPath.'/*.php') as $file) {
                unlink($file);
            }
        }

        // RE-GENERATION
        $output->writeln('<info>→ Génération d’une nouvelle migration...</info>');
        $make = new Process(['php', 'bin/console', 'make:migration']);
        $make->run();

        if (!$make->isSuccessful()) {
            $output->writeln('<error>❌ Impossible de générer une migration :</error>');
            $output->writeln('<comment>'.$make->getErrorOutput().'</comment>');
            return false;
        }

        $output->writeln('<info>✔ Nouvelle migration générée.</info>');
        return true;
    }
}
