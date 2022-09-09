<?php

declare(strict_types=1);

namespace Meteia\Http\Functions;

use Psr\Http\Message\ResponseInterface;

function send(ResponseInterface $response)
{
    while (ob_get_level()) {
        ob_end_clean();
    }

    $http_line = sprintf(
        'HTTP/%s %s %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase(),
    );

    if (!headers_sent()) {
        header($http_line, true, $response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
    }

    $stream = $response->getBody();

    if ($stream->isSeekable()) {
        $stream->rewind();
    }

    while (!$stream->eof()) {
        echo $stream->read(1024 * 8);
    }

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
}
