<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

class BinaryResponse extends Response
{
    /**
     * @param array<non-empty-string, array<array-key, string>|string> $headers
     */
    public function __construct(StreamInterface|string $data, int $status = 200, array $headers = [])
    {
        $body = $data instanceof StreamInterface ? $data : self::createStream($data);
        parent::__construct($body, $status, $headers);
    }

    private static function createStream(string $data): StreamInterface
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($data);
        $stream->rewind();

        return $stream;
    }
}
