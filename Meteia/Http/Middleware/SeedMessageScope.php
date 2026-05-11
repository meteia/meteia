<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\DependencyInjection\Container;
use Meteia\Http\RequestMessageScopeSource;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final readonly class SeedMessageScope implements MiddlewareInterface
{
    private const string HEADER = 'X-Correlation-ID';

    public function __construct(
        private Container $container,
        private ProcessId $processId,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $correlationId = $this->correlationFromRequest($request);
        $causationId = CausationId::random();
        $scope = new MessageScope($correlationId, $causationId, $this->processId);

        $request = $request->withAttribute(MessageScope::class, $scope);
        $this->container->set(MessageScope::class, $scope);
        $this->container->set(MessageScopeSource::class, new RequestMessageScopeSource($request));

        $response = $handler->handle($request);

        return $response->withHeader(self::HEADER, (string) $correlationId);
    }

    private function correlationFromRequest(ServerRequestInterface $request): CorrelationId
    {
        $header = trim($request->getHeaderLine(self::HEADER));
        if ($header === '' || !str_starts_with($header, CorrelationId::prefix() . '_')) {
            return CorrelationId::random();
        }

        try {
            return CorrelationId::fromToken($header);
        } catch (Throwable) {
            return CorrelationId::random();
        }
    }
}
