<?php

namespace OpenSky\Bundle\GraphiteBundle\Listener;

use OpenSky\Bundle\GraphiteBundle\GraphiteLogger;
use OpenSky\Bundle\GraphiteBundle\StatsDLogger;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Close any open connections
 */
class ConnectionCleanupListener
{
    protected $statsd;
    protected $graphite;

    /**
     * @param StatsDLogger
     * @param GraphiteLogger
     */
    public function __construct(StatsDLogger $statsd, GraphiteLogger $graphite)
    {
        $this->statsd = $statsd;
        $this->graphite = $graphite;
    }

    /**
     * Handles the onKernelResponse event.
     *
     * @param FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->statsd->closeConnection();
            $this->graphite->closeConnection();
        }
    }
}