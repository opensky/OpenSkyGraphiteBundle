<?php

namespace OpenSky\Bundle\GraphiteBundle\Tests;

use OpenSky\Bundle\GraphiteBundle\StatsDLogger;

/**
 * @group graphite
 */
class StatsDLoggerTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $prefix;

    protected function setUp()
    {
        $this->connection = $this->getMock('OpenSky\Bundle\GraphiteBundle\Connection');
        $this->prefix = 'cheese.';

        $this->logger = new StatsDLogger($this->connection, $this->prefix);
    }

    public function testSampledSend()
    {
        $logger = $this->getMockBuilder('OpenSky\Bundle\GraphiteBundle\StatsDLogger')
            ->setConstructorArgs(array($this->connection, $this->prefix))
            ->setMethods(array('getRandomRate'))
            ->getMock();

        $data = array(
            'foo' => 'bar',
            'baz' => 'boo'
        );

        $logger = $this->getMockBuilder('OpenSky\Bundle\GraphiteBundle\StatsDLogger')
            ->setConstructorArgs(array($this->connection, $this->prefix))
            ->setMethods(array('getRandomRate'))
            ->getMock();

        $this->connection->expects($this->once())
            ->method('write')
            ->with('cheese.foo:bar|@0.6');

        $logger->expects($this->at(0))
            ->method('getRandomRate')
            ->will($this->returnValue(.5));
        $logger->expects($this->at(1))
            ->method('getRandomRate')
            ->will($this->returnValue(.7));

        $logger->send($data, .6);
    }

    public function testSend()
    {
        $data = array(
            'foo' => '27',
            'bar' => '45'
        );

        $this->connection->expects($this->once())
            ->method('open');
        $this->connection->expects($this->exactly(2))
            ->method('write');
        $this->connection->expects($this->at(1))
            ->method('write')
            ->with('cheese.foo:27');
        $this->connection->expects($this->at(2))
            ->method('write')
            ->with('cheese.bar:45');
        $this->connection->expects($this->once())
            ->method('close');

        $this->logger->send($data);
    }

    public function testSendNoPrefix()
    {
        $data = array(
            'foo' => '27',
            'bar' => '45'
        );

        $this->logger = new StatsDLogger($this->connection);
        $this->connection->expects($this->once())
            ->method('open');
        $this->connection->expects($this->exactly(2))
            ->method('write');
        $this->connection->expects($this->at(1))
            ->method('write')
            ->with('foo:27');
        $this->connection->expects($this->at(2))
            ->method('write')
            ->with('bar:45');
        $this->connection->expects($this->once())
            ->method('close');

        $this->logger->send($data);
    }

    public function testSendEmptyData()
    {
        $this->connection->expects($this->never())
            ->method('open');
        $this->connection->expects($this->never())
            ->method('write');
        $this->connection->expects($this->never())
            ->method('close');

        $this->logger->send(array());
    }

    public function testTiming()
    {
        $stat = 'foo';
        $time = 123;

        $this->connection->expects($this->once())
            ->method('write')
            ->with('cheese.foo:123|ms');

        $this->logger->timing($stat, $time);
    }

    public function testIncrement()
    {
        $stat = 'foo';
        $time = 123;

        $this->connection->expects($this->once())
            ->method('write')
            ->with('cheese.foo:1|c');

        $this->logger->increment($stat);
    }

    public function testDecrement()
    {
        $stat = 'foo';
        $time = 123;

        $this->connection->expects($this->once())
            ->method('write')
            ->with('cheese.foo:-1|c');

        $this->logger->decrement($stat);
    }

    public function testUpdateStats()
    {
        $stats = array('foo', 'bar');
        $time = 123;

        $this->connection->expects($this->at(1))
            ->method('write')
            ->with('cheese.foo:1|c');

        $this->connection->expects($this->at(2))
            ->method('write')
            ->with('cheese.bar:1|c');

        $this->logger->updateStats($stats);
    }
}
