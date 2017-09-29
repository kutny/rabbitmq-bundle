<?php

namespace Kutny\RabbitMqBundle\Consumer\Message;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class MessagesManager
{
    private $amqpChannel;
    private $messageParametersGetter;

    public function __construct(
        AMQPChannel $amqpChannel,
        MessageParametersGetter $messageParametersGetter
    ) {
        $this->amqpChannel = $amqpChannel;
        $this->messageParametersGetter = $messageParametersGetter;
    }

    public function getMessageList($queueName, $limitPerRun)
    {
        $messages = [];

        for ($i = 0; $i < $limitPerRun; $i++) {
            $amqpMessage = $this->amqpChannel->basic_get($queueName, false);

            if ($amqpMessage === null) {
                break;
            }

            $messages[] = $this->createMessage($amqpMessage);
        }

        return new MessageList($messages);
    }

    public function acknowledgeAll($maxDeliveryTag)
    {
        $this->amqpChannel->basic_ack($maxDeliveryTag, true);
    }

    public function acknowledge($deliveryTag)
    {
        $this->amqpChannel->basic_ack($deliveryTag);
    }

    public function acknowledgeMultiple(array $deliveryTags)
    {
        foreach ($deliveryTags as $deliveryTag) {
            $this->amqpChannel->basic_ack($deliveryTag);
        }
    }

    public function reject($deliveryTag)
    {
        $this->amqpChannel->basic_reject($deliveryTag, false);
    }

    public function rejectAll($maxDeliveryTag)
    {
        $this->amqpChannel->basic_nack($maxDeliveryTag, true);
    }

    public function rejectOneByOne(MessageList $messageList)
    {
        foreach ($messageList->getMessages() as $message) {
            $this->reject($message->getDeliveryTag());
        }
    }

    public function rejectRequeue($deliveryTag)
    {
        $this->amqpChannel->basic_reject($deliveryTag, true);
    }

    public function rejectRequeueMessages(array $deliveryTags)
    {
        foreach ($deliveryTags as $deliveryTag) {
            $this->rejectRequeue($deliveryTag);
        }
    }

    private function createMessage(AMQPMessage $amqpMessage)
    {
        $messageBody = json_decode($amqpMessage->body);

        $priority = $this->messageParametersGetter->getPriority($amqpMessage);
        $try = $this->messageParametersGetter->getTry($amqpMessage);
        $deliveryTag = $amqpMessage->delivery_info['delivery_tag'];

        return new Message($messageBody, $deliveryTag, $priority, $try);
    }
}
