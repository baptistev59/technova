<?php

namespace App\Install\Util;

/**
 * Récupération/Création du fichier .env.local
 */
class EnvReader
{
    private string $envPath;

    public function __construct(string $projectDir)
    {
        $this->envPath = $projectDir.'/.env.local';
    }

    // Renvoi le chemin vers le fichier .env.local
    public function getEnvPath(): string
    {
        return $this->envPath;
    }

    // Lit le contenu du fichier .env.local
    public function readDatabaseUrl(): ?string
    {
        if (!file_exists($this->envPath)) {
            return null;
        }
        // Récupérer le contenu du fichier
        $content = file_get_contents($this->envPath);
        if ($content === false) {
            return null;
        }

        // Récupérer la valeur de DATABASE_URL
        if (preg_match('/^DATABASE_URL="([^"]+)"/m', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    // Modifie le contenu du fichier .env.local
    public function writeDatabaseUrl(string $databaseUrl): void
    {
        $line = 'DATABASE_URL="'.$databaseUrl."\"\n";
        file_put_contents($this->envPath, $line);
    }
}
