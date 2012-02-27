<?php

namespace OpenSky\Bundle\GraphiteBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EventDispatcherOverridePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // track the original
        $innerEventDispatcher = $container->getDefinition('event_dispatcher');
        $container->setDefinition('opensky.graphite.inner_event_dispatcher', $innerEventDispatcher);

        $container->register('event_dispatcher')
            ->setClass($container->getParameter('opensky.graphite.event_dispatcher.class'))
            ->addArgument(new Reference('opensky.graphite.inner_event_dispatcher'))
            ->addArgument(new Reference('opensky.graphite.statsd.logger'))
        ;
    }
}