<?php

declare(strict_types=1);

use Meteia\Application\RepositoryPath;
use Meteia\Configuration\Configuration;
use Meteia\Http\Configuration\LogPath;
use Meteia\Http\Csrf\CsrfSecretKey;
use Meteia\Http\EndpointMap;
use Meteia\Http\EndpointMaps\PsrEndpointMap;
use Meteia\Http\Endpoints;
use Meteia\Http\HomepageEndpoint;
use Meteia\Http\Host;
use Meteia\Http\Middleware\PsrEndpoints;
use Meteia\Http\MissingHomepageEndpoint;
use Meteia\Http\RequestHandler;
use Meteia\Http\Scheme;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

return [
    ServerRequestInterface::class => static function () {
        $factory = new Psr17Factory();
        $creator = new ServerRequestCreator($factory, $factory, $factory, $factory);

        return $creator->fromGlobals();
    },
    Endpoints::class => PsrEndpoints::class,
    EndpointMap::class => PsrEndpointMap::class,
    HomepageEndpoint::class => MissingHomepageEndpoint::class,
    RequestHandlerInterface::class => RequestHandler::class,
    Scheme::class => static function (): Scheme {
        if (isset($_SERVER['HTTPS'])) {
            return new Scheme('https');
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return new Scheme('https');
        }

        return new Scheme('http');
    },
    Host::class => static function (Configuration $configuration, Scheme $scheme): Host {
        $host = $configuration->string('HTTP_HOST', $_SERVER['HTTP_HOST'] ?? 'example.com');

        return new Host($scheme . '://' . $host);
    },
    CsrfSecretKey::class => static function (Configuration $configuration): CsrfSecretKey {
        $value = $configuration->string('METEIA_CSRF_SECRET_KEY', '');
        if ($value === '') {
            throw new Exception('METEIA_CSRF_SECRET_KEY not set');
        }

        return CsrfSecretKey::fromToken($value);
    },
    LogPath::class => static fn (Configuration $configuration, RepositoryPath $repositoryPath): LogPath => new LogPath(
        $configuration->string('METEIA_LOG_PATH', (string) $repositoryPath),
    ),
];
