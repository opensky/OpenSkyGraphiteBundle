<?php

namespace OpenSky\Bundle\GraphiteBundle\Tests\Event;

use OpenSky\Bundle\GraphiteBundle\Event\GraphiteEventDispatcher;
use OpenSky\Bundle\GraphiteBundle\Connection\LoggingUdpConnection;
use Symfony\Component\EventDispatcher\Event;

/**
 * @group graphite
 */
class GraphiteEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $statsd;
    private $innerDispatcher;
    private $dispatcher;

    protected function setUp()
    {
        $this->statsd = $this->getMockBuilder('OpenSky\Bundle\GraphiteBundle\StatsdLogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->innerDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->setMethods(array_merge(get_class_methods('Symfony\Component\EventDispatcher\EventDispatcherInterface'), array('callableMethod')))
            ->getMock();
        $this->dispatcher = new GraphiteEventDispatcher($this->innerDispatcher, $this->statsd);
    }

    public function testDispatch()
    {
        $event = new Event();

        $this->statsd
            ->expects($this->once())
            ->method('timing')
            ->with('symfony.kernel.event.foo', $this->isType('float'));

        $this->innerDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with('foo', $event);

        $this->dispatcher->dispatch('foo', $event);
    }

    public function testAddListener()
    {
        $this->innerDispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with('foo', 'listener', 100);
        $this->dispatcher->addListener('foo', 'listener', 100);
    }

    public function testAddSubscriber()
    {
        $subscriber = $this->getMock('Symfony\Component\EventDispatcher\EventSubscriberInterface');
        $this->innerDispatcher
            ->expects($this->once())
            ->method('addSubscriber')
            ->with($subscriber);
        $this->dispatcher->addSubscriber($subscriber);
    }

    public function testRemoveListener()
    {
        $this->innerDispatcher
            ->expects($this->once())
            ->method('removeListener')
            ->with('foo', 'listener');
        $this->dispatcher->removeListener('foo', 'listener');
    }

    public function testRemoveSubscriber()
    {
        $subscriber = $this->getMock('Symfony\Component\EventDispatcher\EventSubscriberInterface');
        $this->innerDispatcher
            ->expects($this->once())
            ->method('removeSubscriber')
            ->with($subscriber);
        $this->dispatcher->removeSubscriber($subscriber);
    }

    public function testGetListeners()
    {
        $this->innerDispatcher
            ->expects($this->once())
            ->method('getListeners')
            ->with('foo')
            ->will($this->returnValue('bar'));
        $this->assertEquals('bar', $this->dispatcher->getListeners('foo'));
    }

    public function testHasListeners()
    {
        $this->innerDispatcher
            ->expects($this->once())
            ->method('hasListeners')
            ->with('foo')
            ->will($this->returnValue('bar'));
        $this->assertEquals('bar', $this->dispatcher->hasListeners('foo'));
    }

    public function testCall()
    {
        $this->innerDispatcher
            ->expects($this->once())
            ->method('callableMethod')
            ->with('foo')
            ->will($this->returnValue('bar'));
        $this->assertEquals('bar', $this->dispatcher->callableMethod('foo'));
    }
}
