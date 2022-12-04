<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Psr\Http\Message\UriInterface;

class Uri extends \Laminas\Diactoros\Uri implements UriInterface
{
    public function __construct(...$segments)
    {
        $value = implode('/', $segments);
        parent::__construct($value);
    }

    // public function withPath($path)
    // {
    //    if (strpos((string)$path, '/') !== 0) {
    //        $path = '/' . (string)$path;
    //    }
    //
    //    return new static($this->getZendUri()->setPath($path));
    // }

    public function withQueryData(array $kv): self
    {
        $kv = array_map('strval', $kv);
        ksort($kv);
        $qs = http_build_query($kv);

        return $this->withQuery($qs);
    }

    //
    // public function withScheme($scheme)
    // {
    //    return new static($this->getZendUri()->setScheme($scheme));
    // }
    //
    //
    // public function withUserInfo($user, $password = null)
    // {
    //    return new static($this->getZendUri()->setUserInfo(join(':', [$user, $password])));
    // }
    //
    //
    // private function getZendUri()
    // {
    //    return new \Laminas\Diactoros\Uri($this->value);
    // }
}
