<?php

namespace Kutny\RabbitMqBundle\Queue;

interface IRetryingQueueConfig extends IQueueConfig
{
    public function getMaxTries();

    public function rejectOnException();
}
