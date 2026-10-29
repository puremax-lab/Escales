<?php

// Creates the tables and loads the demo data.
//   php bin/setup.php --sqlite   var/escales.sqlite, no database server needed
//   php bin/setup.php            the database set in config/ (MySQL by default)

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;

$root = dirname(__DIR__);

if (in_array('--sqlite', $argv, true)) {
    $file = $root . '/var/escales.sqlite';
    if (is_file($file)) {
        unlink($file);
    }
    Database::install(Database::connect('sqlite:' . $file));
    echo "SQLite database created: var/escales.sqlite\n";

    $local = $root . '/config/config.local.php';
    if (!is_file($local)) {
        file_put_contents($local, <<<'PHP'
            <?php

            return [
                'debug' => true,
                'db' => ['dsn' => 'sqlite:' . dirname(__DIR__) . '/var/escales.sqlite'],
            ];

            PHP);
        echo "Config written: config/config.local.php\n";
    }
    exit;
}

$config = require $root . '/config/config.php';
Database::install(Database::connect($config['db']['dsn'], $config['db']['user'], $config['db']['password']));
echo "Tables created and demo data loaded.\n";
