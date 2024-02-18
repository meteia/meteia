<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Endpoints;

use Meteia\ErrorHandling\Templates\StackTrace;
use Meteia\Html\Layout;
use Meteia\Http\Responses\HtmlResponse;
use Meteia\Http\Responses\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function Meteia\Html\Elements\el;

class PublicErrorEndpoint implements ErrorEndpoint
{
    public function __construct(private StackTrace $stackTrace, private Layout $layout)
    {
    }

    public function response(\Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        if (str_contains($request->getHeaderLine('Accept'), 'application/json')) {
            return new JsonResponse(
                [
                    'message' => 'An error has occurred. Please try again later.',
                ],
                500,
            );
        }

        $this->layout->head()->title->set('Server Error');
        $this->layout->body()->header->title('Server Error');

        $body = el('p', [], 'An error has occurred. Please try again later.');

        $this->layout->body()->content($body);

        return new HtmlResponse($this->layout, 500);
    }
}
