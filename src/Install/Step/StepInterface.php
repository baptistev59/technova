<?php

namespace App\Install\Step;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface StepInterface
{
    public function getTitle(): string;

    /**
     * Retourne true si l’étape s’est bien passée, false sinon.
     */
    public function execute(InputInterface $input, OutputInterface $output): bool;
}
