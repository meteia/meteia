<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Configuration\EnvironmentConfiguration;

isset($_ENV['APP_ENV_FILE']) && require_once $_ENV['APP_ENV_FILE'];

return [
    Configuration::class => EnvironmentConfiguration::class,
];
