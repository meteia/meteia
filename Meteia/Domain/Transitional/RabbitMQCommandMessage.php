<?php

declare(strict_types=1);

namespace Meteia\Domain\Transitional;

use Meteia\AdvancedMessageQueuing\Contracts\Message;

class RabbitMQCommandMessage implements Message
{
    private $body;

    private $properties;

    public function __construct($body, $properties)
    {
        $this->body = $body;
        $this->properties = $properties;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getProperties()
    {
        return $this->properties;
    }
}
