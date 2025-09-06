<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Http\Configuration\CookieHost;
use Meteia\Http\Cookies\PlaintextCookie;
use Meteia\Http\Cookies\SameSite;
use Meteia\Http\Host;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseCookies implements MiddlewareInterface
{
    /** @var string[] */
    private array $cookies = [];

    public function __construct(
        private readonly Host $host,
        private readonly CookieHost $cookieHost,
    ) {}

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return array_reduce(
            $this->cookies,
            static fn(ResponseInterface $response, string $cookie) => $response->withAddedHeader('Set-Cookie', $cookie),
            $response,
        );
    }

    public function add(
        PlaintextCookie $cookie,
        ?\DateTimeInterface $expiresAt = null,
        bool $httpOnly = true,
        SameSite $sameSite = SameSite::Lax,
    ): void {
        $maxAge = $expiresAt ? $expiresAt->getTimestamp() - time() : null;
        $kvParts = array_filter([
            $cookie->name => $cookie->value,
            'Expires' => $expiresAt?->format(DATE_RFC7231),
            'Max-Age' => $maxAge,
            'Domain' => $this->cookieHost,
            'Path' => '/',
            'SameSite' => $sameSite->value,
            'Secure' => $this->host->getScheme() === 'https' ? true : null,
            'HttpOnly' => $httpOnly ? true : null,
        ]);

        $parts = array_map(
            static fn($key, $val) => $val === true ? $key : $key . '=' . $val,
            array_keys($kvParts),
            $kvParts,
        );

        $this->cookies[] = implode('; ', $parts);
    }
}
