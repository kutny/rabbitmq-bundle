<?php

namespace Kutny\RabbitMqBundle\Consumer\Message;

class MessageList
{
    private $messages;

    /**
     * @param Message[] $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getBodyAttributeValues($attribute)
    {
        return array_map(
            function (Message $message) use ($attribute) {
                return $message->getBody()->$attribute;
            },
            $this->messages
        );
    }

    public function filter(callable $filterFunction)
    {
        return new MessageList(array_filter($this->messages, $filterFunction));
    }

    public function isEmpty()
    {
        return count($this->messages) === 0;
    }

    public function getMaxDeliveryTag()
    {
        return max($this->getDeliveryTags());
    }

    private function getDeliveryTags()
    {
        $deliveryTags = [];

        foreach ($this->messages as $message) {
            $deliveryTags[] = $message->getDeliveryTag();
        }

        return $deliveryTags;
    }
}
