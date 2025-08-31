<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Endpoints;

use Meteia\ErrorHandling\StackTraces\Frames;
use Meteia\ErrorHandling\StackTraces\Line;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConsoleErrorEndpoint implements ErrorEndpoint
{
    public function __construct(
        private readonly Frames $frames,
    ) {}

    #[\Override]
    public function response(\Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $output = sprintf('fatal error: %s %s', $throwable::class, $throwable->getMessage()) . PHP_EOL . PHP_EOL;
        $output .= 'stack backtrace (oldest first)' . PHP_EOL;

        $frames = $this->frames->from($throwable);
        foreach (array_reverse($frames) as $frame) {
            $output .= sprintf('    %s:%d', $frame->path, $frame->lineNumber) . PHP_EOL;

            /** @var Line $line */
            foreach ($frame->fileFragment->lines as $line) {
                $activeLine = $line->shouldHighlight ? '=>' : '  ';
                $output .= sprintf('     %s % 4d:%s', $activeLine, $line->number, $line->text) . PHP_EOL;
            }
            $output .= PHP_EOL;
        }

        return new Response(500, [], $output);
    }
}
