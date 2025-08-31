<?php

declare(strict_types=1);

namespace Meteia\Http;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;

class RequestBody
{
    private ?string $content = null;

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly Serializer $serializer,
    ) {}

    public function content(): string
    {
        if ($this->content === null) {
            $body = $this->request->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            $this->content = $body->getContents();
        }

        return $this->content;
    }

    public function deserialize(string $className, string $format = 'json'): object
    {
        return $this->serializer->deserialize($this->content, $className, $format);
    }
}
