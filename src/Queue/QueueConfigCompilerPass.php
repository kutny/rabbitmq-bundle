<?php

namespace Kutny\RabbitMqBundle\Queue;

use PhpAmqpLib\Connection\AMQPLazyConnection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class QueueConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {
        $rabbitmqQueueConfigs = $containerBuilder->findTaggedServiceIds('rabbitmq.queue.config');

        $partsHolderDefinition = $containerBuilder->getDefinition('old_sound_rabbit_mq.parts_holder');

        foreach ($rabbitmqQueueConfigs as $configServiceName => $params) {
            $definition = new Definition(AmqpQueue::class, [new Reference($configServiceName), new Reference(AMQPLazyConnection::class)]);
            $definition->setAutowired(true);
            $definition->addTag('old_sound_rabbit_mq.base_amqp');

            $amqpQueueServiceName = $configServiceName . '.amqpQueue';

            $containerBuilder->setDefinition($amqpQueueServiceName, $definition);

            $partsHolderDefinition->addMethodCall('addPart', ['old_sound_rabbit_mq.base_amqp', new Reference($amqpQueueServiceName)]);
        }
    }
}
