<?php

namespace App\Install\Step;

use App\Install\Util\DatabaseDsnParser;
use App\Install\Util\EnvReader;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Configuration de la BDD
 */
class ConfigureDatabaseStep implements StepInterface
{
    public function __construct(
        private EnvReader $envReader,
        private DatabaseDsnParser $dsnParser,
    ) {}

    // Récupérer le titre
    public function getTitle(): string
    {
        return 'Configuration de la base de données';
    }

    // Exécuter l'étape
    public function execute(InputInterface $input, OutputInterface $output): bool
    {
        /** @var QuestionHelper $helper */
        $helper = $input->getOption('no-interaction')
            ? null
            : new QuestionHelper();

        $output->writeln('<info>→ Configuration de la base PostgreSQL</info>');

        // Récupérer la configuration actuelle
        $currentDsn = $this->envReader->readDatabaseUrl();
        $defaults = [
            'host' => 'localhost',
            'port' => '5432',
            'db'   => '',
            'user' => '',
            'pass' => '',
        ];

        // Configuration existante
        if ($currentDsn) {
            $output->writeln('<comment>Configuration existante détectée :</comment>');
            $defaults = $this->dsnParser->parse($currentDsn);
            $output->writeln(sprintf(
                'Host: %s, Port: %s, DB: %s, User: %s',
                $defaults['host'],
                $defaults['port'],
                $defaults['db'],
                $defaults['user']
            ));

            if ($helper) {
                $question = new ConfirmationQuestion(
                    'Souhaitez-vous conserver cette configuration ? (Y/n) ',
                    true
                );
                if ($helper->ask($input, $output, $question)) {
                    $output->writeln('<info>✔ Configuration existante conservée.</info>');
                    return true;
                }
            } else {
                $output->writeln('<comment>Mode non interactif : configuration existante conservée.</comment>');
                return true;
            }
        }

        if (!$helper) {
            $output->writeln('<error>Aucune configuration existante et mode non interactif.</error>');
            return false;
        }

        // Pose des questions avec valeurs par défaut
        $qHost = new Question('Hôte PostgreSQL (ex: localhost) : ', $defaults['host']);
        $qPort = new Question('Port PostgreSQL (ex: 5432) : ', $defaults['port']);
        $qDb   = new Question('Nom de la base : ', $defaults['db']);
        $qUser = new Question("Nom d'utilisateur PostgreSQL : ", $defaults['user']);
        $qPass = new Question('Mot de passe PostgreSQL : ', $defaults['pass']);
        $qPass->setHidden(true);
        $qPass->setHiddenFallback(false);

        $host = $helper->ask($input, $output, $qHost);
        $port = $helper->ask($input, $output, $qPort);
        $db   = $helper->ask($input, $output, $qDb);
        $user = $helper->ask($input, $output, $qUser);
        $pass = $helper->ask($input, $output, $qPass);

        // Création de la configuration
        $dsn = $this->dsnParser->build($host, $port, $db, $user, $pass);
        $this->envReader->writeDatabaseUrl($dsn);

        $output->writeln('<info>✔ .env.local mis à jour avec DATABASE_URL</info>');
        $output->writeln($dsn);

        return true;
    }
}
