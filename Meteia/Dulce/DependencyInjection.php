<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\DependencyInjection\Container;
use Meteia\Dulce\EditorUri;
use Meteia\Dulce\Endpoints\ConsoleErrorEndpoint;
use Meteia\Dulce\Endpoints\DeveloperErrorEndpoint;
use Meteia\Dulce\Endpoints\ErrorEndpoint;
use Meteia\Dulce\ErrorClassifications\ErrorClassification;
use Meteia\Dulce\ErrorClassifications\StrictErrorClassification;
use Meteia\Dulce\StackTraces\FrameFilterMeteia;
use Meteia\Dulce\StackTraces\FrameFilters;

return [
    ErrorClassification::class => StrictErrorClassification::class,
    ErrorEndpoint::class => static function (Container $container, Configuration $configuration): ErrorEndpoint {
        if (PHP_SAPI === 'cli') {
            return $container->get(ConsoleErrorEndpoint::class);
        }

        $errorPage = $configuration->string('ERRORS_ENDPOINT', 'public');

        return match (strtolower($errorPage)) {
            'console' => $container->get(ConsoleErrorEndpoint::class),
            default => $container->get(DeveloperErrorEndpoint::class),
        };
    },
    EditorUri::class => static fn (Configuration $configuration): EditorUri => new EditorUri(
        $configuration->string('ERRORS_EDITOR_URI', 'phpstorm://open'),
    ),
    FrameFilters::class => static function (): FrameFilters {
        // FIXME: This feels ugly, not sure how I want to handle this
        return new FrameFilters([new FrameFilterMeteia()]);
    },
];
