<?php

namespace Kutny\RabbitMqBundle\Consumer;

use Exception;

class UnableToProcessMessageException extends Exception
{
    private $requeue;

    public function __construct($requeue = false)
    {
        if ($requeue !== true && $requeue !== false) {
            throw new Exception('Invalid type of input argument');
        }

        $this->requeue = $requeue;

        parent::__construct();
    }

    public function getRequeue()
    {
        return $this->requeue;
    }
}
