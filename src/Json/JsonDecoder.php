<?php

namespace Kutny\RabbitMqBundle\Json;

class JsonDecoder
{
    public function decode($jsonString)
    {
        $data = json_decode($jsonString);

        if ($data === null) {
            throw new \Exception('Unable to parse given JSON message');
        }

        return $data;
    }
}
