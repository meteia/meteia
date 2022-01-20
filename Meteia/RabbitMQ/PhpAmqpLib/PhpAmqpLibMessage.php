<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\PhpAmqpLib;

use Meteia\RabbitMQ\Contracts\Message;

class PhpAmqpLibMessage implements Message
{
    /** @var string */
    private $body;

    /** @var array */
    private $properties;

    /** @var array */
    private $deliveryInfo;

    public function __construct($body, $properties, $deliveryInfo)
    {
        $this->body = $body;
        $this->properties = $properties;
        $this->deliveryInfo = $deliveryInfo;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return array
     */
    public function getDeliveryInfo()
    {
        return $this->deliveryInfo;
    }
}
