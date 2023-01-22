<?php

declare(strict_types=1);

namespace Meteia\Http;

use Psr\Http\Message\ServerRequestInterface;

class RequestBody
{
    private string|null $content = null;


    public function __construct(private readonly ServerRequestInterface $request)
    {
    }


    public function content(): string
    {
        if (is_null($this->content)) {
            $this->content = $this->request->getBody()->getContents();
        }

        return $this->content;
    }
}
