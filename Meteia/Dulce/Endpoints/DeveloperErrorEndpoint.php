<?php

declare(strict_types=1);

namespace Meteia\Dulce\Endpoints;

use Meteia\Dulce\Templates\StackTrace;
use Meteia\Html\Layout;
use Meteia\Http\Responses\HtmlResponse;
use Meteia\Http\Responses\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeveloperErrorEndpoint implements ErrorEndpoint
{
    public function __construct(private StackTrace $stackTrace, private Layout $layout)
    {
    }

    public function response(\Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        if (str_contains($request->getHeaderLine('Accept'), 'application/json')) {
            return new JsonResponse([
                'message' => $throwable->getMessage(),
                'stackTrace' => array_values(
                    array_map(
                        static fn ($frame) => implode(':', [$frame->file, $frame->line]),
                        $this->stackTrace->for($throwable)->stackFrames(),
                    ),
                ),
            ]);
        }

        $this->layout->head()->title->set($throwable->getMessage());
        $this->layout->body()->header->title($throwable->getMessage());
        $this->layout
            ->head()
            ->scripts->load(
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js',
                false,
                false,
                'sha256-/BfiIkHlHoVihZdc6TFuj7MmJ0TWcWsMXkeDFwhi0zw=',
                'anonymous',
            )
        ;
        $this->layout
            ->head()
            ->stylesheets->load('https://fonts.googleapis.com/css?family=Inconsolata|Geo', '', 'anonymous')
        ;
        $this->layout
            ->head()
            ->stylesheets->load(
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/tomorrow-night.min.css',
                'sha256-2wL88NKUqvJi/ExflDzkzUumjUM73mcK2gBvBBeLvTk=',
                'anonymous',
            )
        ;

        $this->layout->body()->content($this->stackTrace->for($throwable));

        return new HtmlResponse($this->layout, 500);
    }
}
