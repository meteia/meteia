<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

use Meteia\Bluestone\Contracts\Renderable;

class HtmlResponse extends \Laminas\Diactoros\Response\HtmlResponse
{
    public function __construct(Renderable $renderable, $status = 200, array $headers = [])
    {
        parent::__construct((string) $renderable, $status, $headers);
    }
}
