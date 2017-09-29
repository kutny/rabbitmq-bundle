<?php

namespace Kutny\RabbitMqBundle\Queue;

use OldSound\RabbitMqBundle\RabbitMq\BaseAmqp;
use PhpAmqpLib\Connection\AbstractConnection;

class AmqpQueue extends BaseAmqp
{
    public function __construct(
        IQueueConfig $queueConfig,
        AbstractConnection $connection
    ) {
        parent::__construct($connection);

        $this->exchangeOptions = array_merge($this->exchangeOptions, $this->getExchangeOptions($queueConfig));
        $this->setQueueOptions($this->getQueueOptions($queueConfig));
    }

    private function getExchangeOptions(IQueueConfig $queueConfig)
    {
        return [
            'name' => $queueConfig->getExchangeName(),
            'declare' => false
        ];
    }

    private function getQueueOptions(IQueueConfig $queueConfig)
    {
        return [
            'name' => $queueConfig->getQueueName(),
            'routing_keys' => $queueConfig->getRoutingKeys(),
            'arguments' => $queueConfig->getQueueArguments(),
            'durable' => $queueConfig->isDurable(),
        ];
    }
}
