<?php

namespace OpenSky\Bundle\GraphiteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OpenSkyGraphiteExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('graphite.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $container->setParameter('opensky.graphite.prefix', $config['prefix']);
        $graphiteConnection = $config['connection'];
        switch($graphiteConnection) {
            case 'udp':
                if (empty($config['host']) || empty($config['port'])) {
                    throw new InvalidConfigurationException('A graphite udp connection requires a host and port');
                }
                $container->setParameter('opensky.graphite.host', $config['host']);
                $container->setParameter('opensky.graphite.port', $config['port']);
                $container->setAlias('opensky.graphite.connection', 'opensky.graphite.connection.udp');
                break;
            case 'logging':
                $container->setAlias('opensky.graphite.connection', 'opensky.graphite.connection.logging');
                break;
            case 'null':
                $container->setAlias('opensky.graphite.connection', 'opensky.graphite.connection.null');
                break;
            default:
                throw new InvalidConfigurationException(sprintf('graphite.conneciton must be (udp|logging|null) "%s" provided', $graphiteConnection));
                break;
        }

        if (empty($config['statsd']['prefix'])) {
            $container->setParameter('opensky.graphite.statsd.prefix', $config['prefix']);
        } else {
            $container->setParameter('opensky.graphite.statsd.prefix', $config['statsd']['prefix']);
        }

        $statsdConnection = $config['statsd']['connection'];
        switch($statsdConnection) {
            case 'udp':
                if ($config['statsd']['host'] === null || $config['statsd']['port'] === null) {
                    throw new InvalidConfigurationException('A graphite statsd udp connection requires a host and port');
                }
                $container->setParameter('opensky.graphite.statsd.host', $config['statsd']['host']);
                $container->setParameter('opensky.graphite.statsd.port', $config['statsd']['port']);
                $container->setAlias('opensky.graphite.statsd.connection', 'opensky.graphite.statsd.connection.udp');
                break;
            case 'logging':
                $container->setAlias('opensky.graphite.statsd.connection', 'opensky.graphite.statsd.connection.logging');
                break;
            case 'null':
                $container->setAlias('opensky.graphite.statsd.connection', 'opensky.graphite.connection.null');
                break;
            default:
                throw new InvalidConfigurationException(sprintf('graphite.statsd.conneciton must be (udp|logging|null) "%s" provided', $statsdConnection));
                break;
        }
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'opensky_graphite';
    }
}
