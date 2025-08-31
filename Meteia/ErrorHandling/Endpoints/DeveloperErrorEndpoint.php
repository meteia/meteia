<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Endpoints;

use Meteia\ErrorHandling\Templates\StackTrace;
use Meteia\Html\Layout;
use Meteia\Http\Responses\HtmlResponse;
use Meteia\Http\Responses\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeveloperErrorEndpoint implements ErrorEndpoint
{
    public function __construct(
        private StackTrace $stackTrace,
        private Layout $layout,
    ) {}

    #[\Override]
    public function response(\Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        if (str_contains($request->getHeaderLine('Accept'), 'application/json')) {
            return new JsonResponse([
                'message' => $throwable->getMessage(),
                'stackTrace' => array_values(array_map(static fn($frame) => implode(':', [
                    $frame->file,
                    $frame->line,
                ]), $this->stackTrace->for($throwable)->stackFrames())),
            ], 500);
        }

        $this
            ->layout->head()
            ->title->set($throwable->getMessage());
        $this
            ->layout->body()
            ->header->title($throwable->getMessage());

        $this->layout->body()->content($this->stackTrace->for($throwable));

        return new HtmlResponse($this->layout, 500);
    }
}
