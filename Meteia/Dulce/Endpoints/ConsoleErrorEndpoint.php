<?php

declare(strict_types=1);

namespace Meteia\Dulce\Endpoints;

use Meteia\Dulce\StackTraces\Frames;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ConsoleErrorEndpoint implements ErrorEndpoint
{
    /**
     * @var Frames
     */
    private $frames;

    public function __construct(Frames $frames)
    {
        $this->frames = $frames;
    }

    public function response(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $output = 'fatal error: ' . $throwable->getMessage() . PHP_EOL . PHP_EOL;
        $output .= 'stack backtrace (oldest first)' . PHP_EOL;

        $frames = $this->frames->from($throwable);
        foreach (array_reverse($frames) as $frame) {
            $output .= sprintf('    %s:%d', $frame->path(), $frame->lineNumber()) . PHP_EOL;
            foreach ($frame->fileFragment()->lines() as $line) {
                $activeLine = $line->shouldHighlight() ? '=>' : '  ';
                $output .= sprintf('     %s % 4d:%s', $activeLine, $line->number(), $line->text()) . PHP_EOL;
            }
            $output .= PHP_EOL;
        }

        return new Response(500, [], $output);
    }
}
