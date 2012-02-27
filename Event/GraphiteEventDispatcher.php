<?php

namespace OpenSky\Bundle\GraphiteBundle\Event;

use OpenSky\Bundle\GraphiteBundle\StatsDLogger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Log all kernel events
 */
class GraphiteEventDispatcher implements EventDispatcherInterface
{
    protected $statsd;
    protected $innerDispatcher;

    /**
     * @param StatsDLogger
     */
    public function __construct(EventDispatcherInterface $innerDispatcher, StatsDLogger $statsd)
    {
        $this->innerDispatcher = $innerDispatcher;
        $this->statsd = $statsd;
    }

    /**
     * @see Symfony\Component\EventDispatcher.EventDispatcherInterface::dispatch()
     */
    public function dispatch($eventName, Event $event = null)
    {
        $startTime = microtime(true);
        $this->innerDispatcher->dispatch($eventName, $event);
        $totalTime = (microtime(true) - $startTime) * 1000;
        $this->statsd->timing(sprintf('symfony.kernel.event.%s', $eventName), $totalTime);
    }

    /**
     * @see Symfony\Component\EventDispatcher.EventDispatcherInterface::addListener()
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->innerDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @see Symfony\Component\EventDispatcher.EventDispatcherInterface::addSubscriber()
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->innerDispatcher->addSubscriber($subscriber);
    }

    /**
     * @see Symfony\Component\EventDispatcher.EventDispatcherInterface::removeListener()
     */
    public function removeListener($eventName, $listener)
    {
        $this->innerDispatcher->removeListener($eventName, $listener);
    }

    /**
     * @see Symfony\Component\EventDispatcher.EventDispatcherInterface::removeSubscriber()
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->innerDispatcher->removeSubscriber($subscriber);
    }

    /**
     * @see Symfony\Component\EventDispatcher.EventDispatcherInterface::getListeners()
     */
    public function getListeners($eventName = null)
    {
        return $this->innerDispatcher->getListeners($eventName);
    }

    /**
     * @see Symfony\Component\EventDispatcher.EventDispatcherInterface::hasListeners()
     */
    public function hasListeners($eventName = null)
    {
        return $this->innerDispatcher->hasListeners($eventName);
    }

    /**
     * Proxy all remaing methods to the inner event dispatcher.
     *
     * @param type $method
     * @param type $args
     * @return type
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->innerDispatcher, $method), $args);
    }
}
