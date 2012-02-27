<?php

namespace OpenSky\Bundle\GraphiteBundle\Tests\Connection;

use OpenSky\Bundle\GraphiteBundle\Connection\LoggingConnection;

/**
 * @group graphite
 */
class LoggingConnectionTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $logger;

    protected function setUp()
    {
        $this->logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');
        $this->connection = new LoggingConnection($this->logger);
    }

    public function testWrite()
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('write: cheese');

        $this->connection->write('cheese');
    }

    public function testOpen()
    {
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Connection opened');

        $this->connection->open();
    }

    public function testClose()
    {
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Connection closed');

        $this->connection->close();
    }
}
