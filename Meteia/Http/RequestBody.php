<?php

declare(strict_types=1);

namespace Meteia\Http;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;

class RequestBody
{
    private string|null $content = null;

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly Serializer $serializer,
    ) {
    }

    public function content(): string
    {
        if (is_null($this->content)) {
            $this->content = $this->request->getBody()->getContents();
        }

        return $this->content;
    }

    public function deserialize(string $className, string $format = 'json'): object
    {
        return $this->serializer->deserialize($this->content, $className, $format);
    }
}
