<?php

namespace Kutny\RabbitMqBundle\DependencyInjection;

use Kutny\RabbitMqBundle\Queue\IQueueConfig;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class KutnyRabbitMqExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('kutny_rabbit_mq.host', $config['host']);
        $container->setParameter('kutny_rabbit_mq.port', $config['port']);
        $container->setParameter('kutny_rabbit_mq.user', $config['user']);
        $container->setParameter('kutny_rabbit_mq.password', $config['password']);
        $container->setParameter('kutny_rabbit_mq.vhost', $config['vhost']);
        $container->setParameter('kutny_rabbit_mq.connection_timeout', $config['connection_timeout']);
        $container->setParameter('kutny_rabbit_mq.read_write_timeout', $config['read_write_timeout']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->registerForAutoconfiguration(IQueueConfig::class)->addTag('rabbitmq.queue.config');
    }
}
