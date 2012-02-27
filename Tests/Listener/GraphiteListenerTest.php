<?php

namespace OpenSky\Bundle\GraphiteBundle\Tests\Event;

use OpenSky\Bundle\CompatEventBundle\EventDispatcher\GraphiteEventDispatcher;
use OpenSky\Bundle\GraphiteBundle\Connection\LoggingUdpConnection;
use OpenSky\Bundle\GraphiteBundle\Listener\GraphiteListener;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @group graphite
 */
class GraphiteListenerTest extends \PHPUnit_Framework_TestCase
{
    private $statsd;
    private $kernel;
    private $listener;

    protected function setUp()
    {
        $this->statsd = $this->getMockBuilder('OpenSky\Bundle\GraphiteBundle\StatsdLogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $this->listener = new GraphiteListener($this->statsd, $this->kernel);
    }

    public function testOnKernelException()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getException')
            ->will($this->returnValue(new \Exception()));

        $this->statsd
            ->expects($this->once())
            ->method('increment')
            ->with('symfony.kernel.exception.Exception');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelControllerOnKernelResponseMasterRequestArray()
    {
        $controllerEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $responseEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMock('MockController');

        $controllerEvent->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(array($controller, 'fooAction')));

        $controllerEvent->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $responseEvent->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $this->statsd
            ->expects($this->at(0))
            ->method('timing')
            ->with(sprintf('symfony.kernel.%s.fooAction.precontroller', get_class($controller)), $this->isType('float'));

        $this->statsd
            ->expects($this->at(1))
            ->method('timing')
            ->with(sprintf('symfony.kernel.%s.fooAction.controller', get_class($controller)), $this->isType('float'));

        $this->statsd
            ->expects($this->at(2))
            ->method('timing')
            ->with(sprintf('symfony.kernel.%s.fooAction.request', get_class($controller)), $this->isType('float'));

        $this->listener->onKernelController($controllerEvent);
        $this->listener->onKernelResponse($responseEvent);
    }

    public function testOnKernelControllerOnKernelResponseMasterRequestInvoke()
    {
        $controllerEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $responseEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMock('MockController');

        $controllerEvent->expects($this->once())
            ->method('getController')
            ->will($this->returnValue($controller));

        $controllerEvent->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $responseEvent->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $this->statsd
            ->expects($this->at(0))
            ->method('timing')
            ->with(sprintf('symfony.kernel.%s.__invoke.precontroller', get_class($controller)), $this->isType('float'));

        $this->statsd
            ->expects($this->at(1))
            ->method('timing')
            ->with(sprintf('symfony.kernel.%s.__invoke.controller', get_class($controller)), $this->isType('float'));

        $this->statsd
            ->expects($this->at(2))
            ->method('timing')
            ->with(sprintf('symfony.kernel.%s.__invoke.request', get_class($controller)), $this->isType('float'));

        $this->listener->onKernelController($controllerEvent);
        $this->listener->onKernelResponse($responseEvent);
    }

    public function testOnKernelControllerOnKernelResponseSubRequestArray()
    {
        $controllerEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $responseEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMock('MockController');

        $controllerEvent->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(array($controller, 'fooAction')));

        $controllerEvent->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));

        $responseEvent->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));

        $this->statsd
            ->expects($this->once())
            ->method('timing')
            ->with(sprintf('symfony.kernel.%s.fooAction.controller', get_class($controller)), $this->isType('float'));

        $this->listener->onKernelController($controllerEvent);
        $this->listener->onKernelResponse($responseEvent);
    }
}
