<?php

namespace Kutny\RabbitMqBundle\Queue;

interface IQueueConfig
{
    const EXCHANGE_TYPE_DIRECT = 'direct';
    const EXCHANGE_TYPE_FANOUT = 'fanout';
    const EXCHANGE_TYPE_TOPIC = 'topic';
    const EXCHANGE_TYPE_HEADERS = 'headers';
    const EXCHANGE_AMQ_DIRECT = 'amq.direct';
    const EXCHANGE_AMQ_FANOUT = 'amq.fanout';
    const EXCHANGE_AMQ_HEADERS = 'amq.headers';
    const EXCHANGE_AMQ_MATCH = 'amq.match';
    const EXCHANGE_AMQ_RABBITMQ_LOG = 'amq.rabbitmq.log';
    const EXCHANGE_AMQ_RABBITMQ_TRACE = 'amq.rabbitmq.trace';
    const EXCHANGE_AMQ_TOPIC = 'amq.topic';

    public function getExchangeName();

    /** @return array */
    public function getRoutingKeys();

    public function getQueueName();

    public function getQueueArguments();

    public function isDurable();

    public function getPrefetchSize();

    public function getPrefetchCount();

    public function getQosOptionsGlobal();
}
