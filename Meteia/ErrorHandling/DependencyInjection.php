<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\DependencyInjection\Container;
use Meteia\ErrorHandling\EditorUri;
use Meteia\ErrorHandling\Endpoints\ConsoleErrorEndpoint;
use Meteia\ErrorHandling\Endpoints\DeveloperErrorEndpoint;
use Meteia\ErrorHandling\Endpoints\ErrorEndpoint;
use Meteia\ErrorHandling\Endpoints\PublicErrorEndpoint;
use Meteia\ErrorHandling\ErrorClassifications\ErrorClassification;
use Meteia\ErrorHandling\ErrorClassifications\StrictErrorClassification;
use Meteia\ErrorHandling\StackTraces\FrameFilterMeteia;
use Meteia\ErrorHandling\StackTraces\FrameFilters;

return [
    ErrorClassification::class => StrictErrorClassification::class,
    ErrorEndpoint::class => static function (Container $container, Configuration $configuration): ErrorEndpoint {
        if (PHP_SAPI === 'cli') {
            return $container->get(ConsoleErrorEndpoint::class);
        }

        $errorPage = $configuration->string('ERROR_HANDLING_ENDPOINT', 'public');

        return match (strtolower($errorPage)) {
            'console' => $container->get(ConsoleErrorEndpoint::class),
            'developer' => $container->get(DeveloperErrorEndpoint::class),
            default => $container->get(PublicErrorEndpoint::class),
        };
    },
    EditorUri::class => static fn (Configuration $configuration): EditorUri => new EditorUri(
        $configuration->string('EDITOR_URI', 'phpstorm://open'),
    ),
    FrameFilters::class => static function (): FrameFilters {
        // FIXME: This feels ugly, not sure how I want to handle this
        return new FrameFilters([new FrameFilterMeteia()]);
    },
];
