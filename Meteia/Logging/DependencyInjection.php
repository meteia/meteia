<?php

declare(strict_types=1);

use Meteia\Application\RepositoryPath;
use Meteia\Logging\DecoratedLog;
use Meteia\Logging\StandardError;
use Meteia\Logging\UdpSystemLog;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (
        CausationId $causationId,
        CorrelationId $correlationId,
        ProcessId $processId,
        RepositoryPath $repositoryPath,
    ) {
        // FIXME : Don't like how this is working out... hmm
        $output = PHP_SAPI === 'cli' ? new StandardError() : new UdpSystemLog();

        // FIXME : In particular this
        return new DecoratedLog($output, $correlationId, $causationId, $processId, $repositoryPath);
    },
];
