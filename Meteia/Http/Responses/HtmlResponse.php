<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

use Meteia\Html\Component;
use Meteia\Html\HtmlEncoder;
use Stringable;

class HtmlResponse extends \Laminas\Diactoros\Response\HtmlResponse
{
    /**
     * @param array<non-empty-string, array<array-key, string>|string> $headers
     */
    public function __construct(
        null|string|Stringable|Component $renderable = null,
        int $status = 200,
        array $headers = [],
    ) {
        $body = $renderable === null ? '' : new HtmlEncoder()->encode($renderable);

        parent::__construct($body, $status, $headers);
    }
}
