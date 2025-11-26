<?php

namespace App\Install;

use App\Install\Step\CheckEnvironmentStep;
use App\Install\Step\ConfigureDatabaseStep;
use App\Install\Step\RunMigrationsStep;
use App\Install\Step\SummaryStep;
use App\Install\Step\TestDatabaseConnectionStep;
use App\Install\Step\StepInterface;
use App\Install\Step\RepairMigrationsStep;
use App\Install\Step\CreateOrResetDatabaseStep;
use App\Install\Step\CreateAdminStep;
use App\Install\Util\DatabaseDsnParser;
use App\Install\Util\EnvReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Commande principali app:install (orchestrateur)
 */
#[AsCommand(
    name: 'app:install',
    description: 'Assistant complet d’installation de TechNova',
)]
class InstallCommand extends Command
{
    
    /** @var StepInterface[] */
    private array $steps = [];

    public function __construct( 
        string $projectDir,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();

        $envReader = new EnvReader($projectDir);
        $dsnParser = new DatabaseDsnParser();

        $this->steps = [
            new CheckEnvironmentStep(),
            new ConfigureDatabaseStep($envReader, $dsnParser),
            new CreateOrResetDatabaseStep($envReader, $dsnParser),
            new TestDatabaseConnectionStep($envReader, $dsnParser),
            new RepairMigrationsStep(),
            new RunMigrationsStep(),
            new CreateAdminStep($em, $passwordHasher),
            new SummaryStep(),
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Instanciation du helper
        /** @var QuestionHelper $helper */
        $helper = new QuestionHelper();

        $output->writeln("\n<info>=== Assistant d’installation TechNova ===</info>\n");

        $index = 1;
        $total = count($this->steps);

        // Exécuter chaque étape
        foreach ($this->steps as $step) {
            $output->writeln(sprintf(
                "<comment>[%d/%d] %s</comment>",
                $index,
                $total,
                $step->getTitle()
            ));

            // Exécuter l'étape
            $ok = $step->execute($input, $output);

            // Si l'exécuton de l'étape a echoué
            if (!$ok) {
                $output->writeln('<error>⚠ Une erreur est survenue dans cette étape.</error>');

                // On propose à l’utilisateur de réessayer ou d’arrêter
                if (!$input->getOption('no-interaction')) {
                    $question = new ConfirmationQuestion(
                        "Souhaitez-vous réessayer cette étape ? (Y/n) ",
                        true
                    );
                    if ($helper->ask($input, $output, $question)) {
                        // On refait cette étape une fois
                        $output->writeln('<comment>→ Nouvelle tentative...</comment>');
                        $ok = $step->execute($input, $output);
                        if (!$ok) {
                            $output->writeln('<error>Installation interrompue.</error>');
                            return Command::FAILURE;
                        }
                    } else {
                        $output->writeln('<error>Installation interrompue par l’utilisateur.</error>');
                        return Command::FAILURE;
                    }
                } else {
                    $output->writeln('<error>Mode non interactif : installation interrompue.</error>');
                    return Command::FAILURE;
                }
            }

            // Afficher le résultat de l'étape
            $index++;
            $output->writeln(''); // ligne vide
        }

        return Command::SUCCESS;
    }
}
