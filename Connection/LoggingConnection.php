<?php

namespace OpenSky\Bundle\GraphiteBundle\Connection;

use OpenSky\Bundle\GraphiteBundle\Connection;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class LoggingConnection implements Connection
{
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function write($data)
    {
        $this->logger->info(sprintf('write: %s', $data));
    }

    public function open()
    {
        $this->logger->debug('Connection opened');
    }

    public function close()
    {
        $this->logger->debug('Connection closed');
    }
}