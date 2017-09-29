<?php

namespace Kutny\RabbitMqBundle\Publisher\Message;

interface IMessage
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    const DELIVERY_MODE_PERSISTENT = 2;

    const EXCHANGE_DIRECT = 'amq.direct';

    public function getBody();

    public function getRoutingKey();

    public function getPriority();

    public function getContentType();

    public function getDeliveryMode();

    public function getExchange();
}
