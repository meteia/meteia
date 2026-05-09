<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

class BinaryResponse extends Response
{
    public function __construct(StreamInterface|string $data, $status = 200, array $headers = [])
    {
        $body = match (true) {
            $data instanceof StreamInterface => $data,
            default => $this->getStream($data),
        };
        parent::__construct($body, $status, $headers);
    }

    #[\Override]
    private function getStream($data): StreamInterface
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($data);
        $stream->rewind();

        return $stream;
    }
}
