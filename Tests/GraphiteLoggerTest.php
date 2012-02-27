<?php

namespace OpenSky\Bundle\GraphiteBundle\Tests;

use OpenSky\Bundle\GraphiteBundle\GraphiteLogger;

/**
 * @group graphite
 */
class GraphiteLoggerTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $prefix;

    protected function setUp()
    {
        $this->connection = $this->getMock('OpenSky\Bundle\GraphiteBundle\Connection');
        $this->prefix = 'cheese.';
    }

    public function testLog()
    {
        $date = new \DateTime();
        $logger = new GraphiteLogger($this->connection, $this->prefix);

        $this->connection->expects($this->once())
            ->method('open');
        $this->connection->expects($this->once())
            ->method('write')
            ->with(sprintf('cheese.foo bar %s', $date->getTimeStamp()));
        $this->connection->expects($this->once())
            ->method('close');

        $logger->log('foo', 'bar', $date);
    }

    public function testLogNoPrefix()
    {
        $date = new \DateTime();
        $logger = new GraphiteLogger($this->connection);

        $this->connection->expects($this->once())
            ->method('open');
        $this->connection->expects($this->once())
            ->method('write')
            ->with(sprintf('foo bar %s', $date->getTimeStamp()));
        $this->connection->expects($this->once())
            ->method('close');

        $logger->log('foo', 'bar', $date);
    }
}
