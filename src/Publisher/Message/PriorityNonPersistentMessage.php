<?php

namespace Kutny\RabbitMqBundle\Publisher\Message;

class PriorityNonPersistentMessage implements IMessage
{
    private $body;
    private $routingKey;
    private $priority;

    public function __construct($body, $routingKey, $priority)
    {
        $this->body = $body;
        $this->routingKey = $routingKey;
        $this->priority = $priority;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getContentType()
    {
        return 'text/plain';
    }

    public function getDeliveryMode()
    {
        IMessage::DELIVERY_MODE_NON_PERSISTENT;
    }

    public function getExchange()
    {
        IMessage::EXCHANGE_DIRECT;
    }
}
