<?php

namespace Kutny\RabbitMqBundle\Consumer;

interface IParamConverter
{
    /**
     * @return array
     */
    public function convert(\stdClass $message, $try, $priority);
}
