<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\Domain\Contracts\Comparable;
use Meteia\Domain\Contracts\Identity\Uri as UriContract;
use NoDiscard;
use Override;
use SensitiveParameter;
use Uri\Rfc3986\Uri as Rfc3986Uri;

class Uri implements UriContract, Comparable
{
    public function __construct(
        private string $uri,
    ) {}

    #[Override]
    public function __toString(): string
    {
        return $this->uri;
    }

    #[Override]
    public function toNative(): string
    {
        return $this->uri;
    }

    #[Override]
    public function compareTo(Comparable $other): int
    {
        return strcasecmp($this->uri, (string) $other->toNative());
    }

    #[Override]
    public function getScheme(): string
    {
        return $this->parsed()->getScheme() ?? '';
    }

    #[Override]
    public function getAuthority(): string
    {
        $parsed = $this->parsed();
        $host = $parsed->getHost();
        if ($host === null) {
            return '';
        }
        $userInfo = $parsed->getUserInfo();
        $port = $parsed->getPort();

        return ($userInfo !== null ? $userInfo . '@' : '') . $host . ($port !== null ? ':' . $port : '');
    }

    #[Override]
    public function getUserInfo(): string
    {
        return $this->parsed()->getUserInfo() ?? '';
    }

    #[Override]
    public function getHost(): string
    {
        return $this->parsed()->getHost() ?? '';
    }

    #[Override]
    public function getPort(): ?int
    {
        return $this->parsed()->getPort();
    }

    #[Override]
    public function getPath(): string
    {
        return $this->parsed()->getPath() ?? '';
    }

    #[Override]
    public function getQuery(): string
    {
        return $this->parsed()->getQuery() ?? '';
    }

    #[Override]
    public function getFragment(): string
    {
        return $this->parsed()->getFragment() ?? '';
    }

    #[NoDiscard]
    #[Override]
    public function withScheme(string $scheme): self
    {
        return new self(
            $this
                ->parsed()
                ->withScheme($scheme === '' ? null : $scheme)
                ->toString(),
        );
    }

    #[NoDiscard]
    #[Override]
    public function withUserInfo(string $user, #[SensitiveParameter] ?string $password = null): self
    {
        $userInfo = match (true) {
            $user === '' => null,
            $password === null => $user,
            default => $user . ':' . $password,
        };

        return new self($this->parsed()->withUserInfo($userInfo)->toString());
    }

    #[NoDiscard]
    #[Override]
    public function withHost(string $host): self
    {
        return new self(
            $this
                ->parsed()
                ->withHost($host === '' ? null : $host)
                ->toString(),
        );
    }

    #[NoDiscard]
    #[Override]
    public function withPort(?int $port): self
    {
        return new self($this->parsed()->withPort($port)->toString());
    }

    #[NoDiscard]
    #[Override]
    public function withPath(string $path): self
    {
        return new self($this->parsed()->withPath($path)->toString());
    }

    #[NoDiscard]
    #[Override]
    public function withQuery(string $query): self
    {
        return new self(
            $this
                ->parsed()
                ->withQuery($query === '' ? null : $query)
                ->toString(),
        );
    }

    public function withQueryData(array $kv): self
    {
        $kv = array_map('strval', $kv);
        ksort($kv);
        $qs = http_build_query($kv);

        return $this->withQuery($qs);
    }

    #[NoDiscard]
    #[Override]
    public function withFragment(string $fragment): self
    {
        return new self(
            $this
                ->parsed()
                ->withFragment($fragment === '' ? null : $fragment)
                ->toString(),
        );
    }

    private function parsed(): Rfc3986Uri
    {
        return new Rfc3986Uri($this->uri);
    }
}
