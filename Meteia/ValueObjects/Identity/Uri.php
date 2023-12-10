<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Psr\Http\Message\UriInterface;

class Uri extends \Laminas\Diactoros\Uri implements UriInterface, \JsonSerializable
{
    public function __construct(...$segments)
    {
        $value = implode('/', $segments);
        parent::__construct($value);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function withQueryData(array $kv): self
    {
        $kv = array_map('strval', $kv);
        ksort($kv);
        $qs = http_build_query($kv);

        return $this->withQuery($qs);
    }
}
