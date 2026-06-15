<?php

declare(strict_types=1);

use Meteia\Bootstrap\RepositoryPath;
use Meteia\Configuration\Configuration;
use Meteia\Logging\ConfiguredLogOutput;
use Meteia\Logging\DecoratedLog;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (
        MessageScopeSource $scopeSource,
        RepositoryPath $repositoryPath,
        Configuration $configuration,
    ): LoggerInterface {
        $output = ConfiguredLogOutput::fromEnvironment($configuration)->create();

        return new DecoratedLog($output, $scopeSource, $repositoryPath);
    },
];