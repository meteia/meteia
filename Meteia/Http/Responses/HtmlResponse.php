<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

class HtmlResponse extends \Laminas\Diactoros\Response\HtmlResponse
{
    public function __construct(null|string|\Stringable $renderable = null, $status = 200, array $headers = [])
    {
        parent::__construct((string) $renderable, $status, $headers);
    }
}
