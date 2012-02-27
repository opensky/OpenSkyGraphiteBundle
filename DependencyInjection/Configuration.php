<?php

namespace OpenSky\Bundle\GraphiteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * @see Symfony\Component\Config\Definition\ConfigurationInterface::getConfigTreeBuilder()
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opensky_graphite');

        $rootNode
            ->children()
                ->scalarNode('connection')->defaultValue('udp')->treatNullLike('null')->cannotBeEmpty()->end()
                ->scalarNode('host')->defaultNull()->end()
                ->scalarNode('port')->defaultValue(2003)->end()
                ->scalarNode('prefix')->defaultValue('')->end()
                ->arrayNode('statsd')
                    ->children()
                        ->scalarNode('connection')->defaultValue('udp')->treatNullLike('null')->cannotBeEmpty()->end()
                        ->scalarNode('host')->defaultNull()->end()
                        ->scalarNode('port')->defaultValue(8125)->end()
                        ->scalarNode('prefix')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
