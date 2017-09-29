<?php

namespace Kutny\RabbitMqBundle\Publisher\Message;

class NonPersistentMessage implements IMessage
{
    private $body;
    private $routingKey;

    public function __construct($body, $routingKey)
    {
        $this->body = $body;
        $this->routingKey = $routingKey;
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
        return null;
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
