<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Database\Database;

return [
    Database::class => function (Configuration $configuration): Database {
        $hostname = $configuration->string('MYSQL_HOST', '127.0.0.1');
        $database = $configuration->string('MYSQL_DATABASE', 'meteia');
        $port = $configuration->int('MYSQL_PORT', 3306);
        $username = $configuration->string('MYSQL_USER', 'meteia');
        $password = $configuration->string('MYSQL_PWD', 'meteia');

        $dsn = sprintf('mysql:dbname=%s;host=%s;port=%d;charset=utf8mb4', $database, $hostname, $port);

        return new Database($dsn, $username, $password);
    },
];
