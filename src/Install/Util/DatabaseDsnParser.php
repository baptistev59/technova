<?php

namespace App\Install\Util;

/**
 * Récupère les données DATABASE_URL et les affiche en tableau
 * Et recrécupère les données saisies et crée DATABASE_URL
 */
class DatabaseDsnParser
{
    /**
     * Parse une DATABASE_URL PostgreSQL et renvoie un tableau :
     * [host, port, db, user, pass]
     */
    public function parse(string $dsn): array
    {
        $defaults = [
            'host' => 'localhost',
            'port' => '5432',
            'db'   => '',
            'user' => '',
            'pass' => '',
        ];

        $parts = parse_url($dsn);
        if ($parts === false) {
            return $defaults;
        }

        if (!empty($parts['host'])) {
            $defaults['host'] = $parts['host'];
        }
        if (!empty($parts['port'])) {
            $defaults['port'] = (string) $parts['port'];
        }
        if (!empty($parts['user'])) {
            $defaults['user'] = $parts['user'];
        }
        if (!empty($parts['pass'])) {
            $defaults['pass'] = $parts['pass'];
        }
        if (!empty($parts['path'])) {
            $defaults['db'] = ltrim($parts['path'], '/');
        }

        return $defaults;
    }

    /**
     * Construit une DATABASE_URL PostgreSQL à partir des morceaux.
     */
    public function build(string $host, string $port, string $db, string $user, string $pass): string
    {
        return sprintf(
            'postgresql://%s:%s@%s:%s/%s?serverVersion=16&charset=utf8',
            $user,
            $pass,
            $host,
            $port,
            $db
        );
    }
}
