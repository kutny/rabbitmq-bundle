<?php

namespace Kutny\RabbitMqBundle\Consumer\Message;

use PhpAmqpLib\Message\AMQPMessage;

class MessageParametersGetter
{
    public function getTry(AMQPMessage $message)
    {
        if ($message->has('application_headers')) {
            $nativeData = $message->get('application_headers')->getNativeData();

            if ($nativeData === []) {
                return 1;
            }

            return $nativeData['x-death'][0]['count'] + 1;
        }
        else {
            return 1;
        }
    }

    public function getPriority(AMQPMessage $message)
    {
        $properties = $message->get_properties();

        if (array_key_exists('priority', $properties)) {
            return $properties['priority'];
        }
        else {
            return null;
        }
    }
}
