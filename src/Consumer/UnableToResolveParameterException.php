<?php

namespace Kutny\RabbitMqBundle\Consumer;

use Exception;

class UnableToResolveParameterException extends Exception
{
    private $parameterName;

    public function __construct($parameterName)
    {
        $this->parameterName = $parameterName;

        parent::__construct();
    }

    public function getParameterName()
    {
        return $this->parameterName;
    }
}
