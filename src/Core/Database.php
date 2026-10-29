<?php

namespace App\Core;

use PDO;

final class Database
{
    /** PDO set up to throw on errors and to use real prepared statements. */
    public static function connect(string $dsn, string $user = '', string $password = ''): PDO
    {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            // SQLite ignores foreign keys unless asked.
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        return $pdo;
    }

    /** Creates the tables and loads the demo data (used by bin/setup.php and the tests). */
    public static function install(PDO $pdo, bool $withDemoData = true): void
    {
        $dir = dirname(__DIR__, 2) . '/database';
        $schema = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'schema.sqlite.sql' : 'schema.sql';
        $pdo->exec(file_get_contents("$dir/$schema"));
        if ($withDemoData) {
            $pdo->exec(file_get_contents("$dir/seed.sql"));
        }
    }
}
