<?php

namespace Kutny\RabbitMqBundle;

use Kutny\RabbitMqBundle\Consumer\ConsumerRunnersCompilerPass;
use Kutny\RabbitMqBundle\Queue\QueueConfigCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KutnyRabbitMqBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new QueueConfigCompilerPass());
        $container->addCompilerPass(new ConsumerRunnersCompilerPass());
    }
}
