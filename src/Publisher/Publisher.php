<?php

namespace Kutny\RabbitMqBundle\Publisher;

use Kutny\RabbitMqBundle\Publisher\Message\IMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher
{
    private $amqpChannel;

    public function __construct(
        AMQPChannel $amqpChannel
    ) {
        $this->amqpChannel = $amqpChannel;
    }

    public function publish(IMessage $message)
    {
        $amqpMessage = $this->createAmqpMessage($message);
        $this->amqpChannel->basic_publish($amqpMessage, $message->getExchange(), $message->getRoutingKey());
    }

    /**
     * @param IMessage[] $messages
     */
    public function publishBatch(array $messages)
    {
        if (count($messages) === 0) {
            return;
        }

        foreach ($messages as $message) {
            $amqpMessage = $this->createAmqpMessage($message);

            $this->amqpChannel->batch_basic_publish($amqpMessage, $message->getExchange(), $message->getRoutingKey());
        }

        $this->amqpChannel->publish_batch();
    }

    public function __destruct()
    {
        $this->amqpChannel->close();

        $connection = $this->amqpChannel->getConnection();

        if ($connection && $connection->isConnected()) {
            $connection->close();
        }
    }

    private function createAmqpMessage(IMessage $message)
    {
        $parameters = [
            'content_type' => $message->getContentType(),
            'delivery_mode' => $message->getDeliveryMode(),
        ];

        if ($message !== null) {
            $parameters['priority'] = $message->getPriority();
        }

        return new AMQPMessage($message->getBody(), $parameters);
    }
}
