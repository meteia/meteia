<?php

declare(strict_types=1);

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;
use Meteia\Configuration\Configuration;
use Meteia\Configuration\EnvironmentConfiguration;

return [
    Configuration::class => EnvironmentConfiguration::class,
    Inflector::class => fn () => InflectorFactory::createForLanguage(Language::ENGLISH)->build(),
];
