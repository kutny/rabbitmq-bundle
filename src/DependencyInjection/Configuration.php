<?php

namespace Kutny\RabbitMqBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kutny_rabbit_mq');

        $rootNode
            ->children()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultValue(5672)->end()
                ->scalarNode('user')->defaultValue('guest')->end()
                ->scalarNode('password')->defaultValue('guest')->end()
                ->scalarNode('vhost')->defaultValue('/')->end()
                ->scalarNode('connection_timeout')->defaultValue(3)->end()
                ->scalarNode('read_write_timeout')->defaultValue(3)->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
