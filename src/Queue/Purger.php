<?php

namespace Kutny\RabbitMqBundle\Queue;

use PhpAmqpLib\Channel\AMQPChannel;

class Purger
{
    private $amqpChannel;

    public function __construct(
        AMQPChannel $amqpChannel
    ) {
        $this->amqpChannel = $amqpChannel;
    }

    public function purge($queueName)
    {
        $this->amqpChannel->queue_purge($queueName);
    }
}
