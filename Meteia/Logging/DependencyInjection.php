<?php

declare(strict_types=1);

use Meteia\Application\RepositoryPath;
use Meteia\Logging\DecoratedLog;
use Meteia\Logging\Logfmt;
use Meteia\Logging\StandardError;
use Meteia\Logging\UdpSystemLog;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => function (RepositoryPath $repositoryPath) {
        // FIXME : Don't like how this is working out... hmm
        $output = (PHP_SAPI === 'cli') ? new StandardError() : new UdpSystemLog();

        // FIXME : In particular this
        return new DecoratedLog($output, $repositoryPath);
    },
];
