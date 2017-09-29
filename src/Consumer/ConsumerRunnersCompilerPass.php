<?php

namespace Kutny\RabbitMqBundle\Consumer;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConsumerRunnersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {
        $consumerRunnerServiceNames = $containerBuilder->findTaggedServiceIds('rabbitmq.consumer_runner');
        $workerServiceNames = $containerBuilder->findTaggedServiceIds('rabbitmq.worker');

        $this->createOldSoundConsumerServices($consumerRunnerServiceNames, $containerBuilder);
        $this->fillConsumerAliasesStorage($consumerRunnerServiceNames, $workerServiceNames, $containerBuilder);
    }

    private function createOldSoundConsumerServices(array $consumerRunnerServiceNames, ContainerBuilder $containerBuilder)
    {
        foreach ($consumerRunnerServiceNames as $consumerRunnerServiceName => $params) {
            $aliasServiceName = 'old_sound_rabbit_mq.' . $this->getAlias($params) . '_consumer';
            $alias = new Alias($consumerRunnerServiceName);
            $alias->setPublic(true);

            $containerBuilder->setAlias($aliasServiceName, $alias);
        }
    }

    private function fillConsumerAliasesStorage(array $consumerRunnerServiceNames, array $workerServiceNames, ContainerBuilder $containerBuilder)
    {
        $consumers = [];

        foreach ($consumerRunnerServiceNames as $params) {
            $consumers[] = $this->getAlias($params);
        }

        foreach ($workerServiceNames as $params) {
            $consumers[] = $this->getAlias($params);
        }

        $definition = $containerBuilder->getDefinition(ConsumerListCommand::class);
        $definition->addArgument(array_unique($consumers));
    }

    private function getAlias(array $serviceParams)
    {
        $tagParams = $serviceParams[0];

        if (!array_key_exists('alias', $tagParams)) {
            throw new \Exception('Consumer runner must have alias defined');
        }

        return $tagParams['alias'];
    }
}
