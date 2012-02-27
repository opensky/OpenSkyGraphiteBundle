<?php

namespace OpenSky\Bundle\GraphiteBundle\Listener;

use OpenSky\Bundle\GraphiteBundle\StatsDLogger;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Log native Symfony events
 */
class GraphiteListener
{
    protected $statsd;
    protected $kernel;
    protected $startTime;
    protected $controller;

    /**
     * @param StatsDLogger
     * @param KernelInterface
     */
    public function __construct(StatsDLogger $statsd, KernelInterface $kernel)
    {
        $this->statsd = $statsd;
        $this->kernel = $kernel;
    }

    /**
     * Handles the onKernelException event.
     *
     * @param GetResponseForExceptionEvent $event A GetResponseForExceptionEvent instance
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->statsd->increment(sprintf('symfony.kernel.exception.%s', $this->classToString($event->getException())));
    }

    /**
     * Handles the onKernelController event.
     *
     * @param FilterResponseEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (is_array($controller)) {
            list($controller, $method) = $controller;
        } else {
            $method = '__invoke';
        }
        $controller = sprintf('%s.%s', $this->classToString($controller), $method);

        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->statsd->timing(sprintf('symfony.kernel.%s.precontroller', $controller), $this->getTimeSinceKernelStart());
        }

        $this->controller = $controller;
        $this->startTime = microtime(true);
    }

    /**
     * Handles the onKernelResponse event.
     *
     * @param FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->statsd->timing(sprintf('symfony.kernel.%s.controller', $this->controller), (microtime(true) - $this->startTime) * 1000);

        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->statsd->timing(sprintf('symfony.kernel.%s.request', $this->controller), $this->getTimeSinceKernelStart());
        }
    }

    protected function getTimeSinceKernelStart()
    {
        return (microtime(true) - $this->kernel->getStartTime()) * 1000;
    }

    protected function classToString($object)
    {
        if (is_object($object)) {
            return str_replace('\\', '.', get_class($object));
        }
    }
}
