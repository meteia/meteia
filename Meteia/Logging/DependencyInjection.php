<?php

declare(strict_types=1);

use Meteia\Bootstrap\RepositoryPath;
use Meteia\Logging\DecoratedLog;
use Meteia\Logging\StandardError;
use Meteia\Logging\UdpSystemLog;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (
        MessageScopeSource $scopeSource,
        RepositoryPath $repositoryPath,
    ): LoggerInterface {
        $output = PHP_SAPI === 'cli' ? new StandardError() : new UdpSystemLog();

        return new DecoratedLog($output, $scopeSource, $repositoryPath);
    },
];
