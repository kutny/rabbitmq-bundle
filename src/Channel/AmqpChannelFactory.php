<?php

namespace Kutny\RabbitMqBundle\Channel;

use PhpAmqpLib\Connection\AbstractConnection;

class AmqpChannelFactory
{
    private $connection;

    public function __construct(
        AbstractConnection $connection
    ) {
        $this->connection = $connection;
    }

    public function createChannelFromPid()
    {
        $channelId = getmypid() % 65535;

        return $this->connection->channel($channelId);
    }
}
