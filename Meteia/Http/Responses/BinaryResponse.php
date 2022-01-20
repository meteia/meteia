<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

use Laminas\Diactoros\Stream;

class BinaryResponse extends \Laminas\Diactoros\Response\HtmlResponse
{
    public function __construct(string $data, $status = 200, array $headers = [])
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($data);
        $body->rewind();

        parent::__construct((string) $body, $status, $headers);
    }
}
