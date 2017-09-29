<?php

namespace Kutny\RabbitMqBundle\Consumer\Message;

class Message
{
    private $body;
    private $deliveryTag;
    private $priority;
    private $try;

    public function __construct(\stdClass $body, $deliveryTag, $priority, $try)
    {
        $this->body = $body;
        $this->deliveryTag = $deliveryTag;
        $this->priority = $priority;
        $this->try = $try;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getDeliveryTag()
    {
        return $this->deliveryTag;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getTry()
    {
        return $this->try;
    }
}
