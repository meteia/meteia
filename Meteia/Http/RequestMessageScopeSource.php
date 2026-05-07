<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\Http\Exceptions\MissingMessageScope;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Psr\Http\Message\ServerRequestInterface;

final readonly class RequestMessageScopeSource implements MessageScopeSource
{
    public function __construct(
        private ServerRequestInterface $request,
    ) {}

    #[\Override]
    public function current(): MessageScope
    {
        $scope = $this->request->getAttribute(MessageScope::class);
        if (!$scope instanceof MessageScope) {
            throw new MissingMessageScope();
        }

        return $scope;
    }
}
