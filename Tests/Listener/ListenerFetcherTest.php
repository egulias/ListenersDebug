<?php

namespace Egulias\ListenersDebug\Tests\Listener;

use Egulias\ListenersDebug\Listener\ListenerFetcher;

/**
 * ListenerFetcherTest
 *
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class ListenerFetcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider eventListenerDataProvider
     */
    public function testFetchListeners($eventData)
    {
        $defMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $defMock->expects($this->once())->method('getTags')->will(
            $this->returnValue(array($eventData['name'] => array()))
        );
        $defMock->expects($this->once())->method('isPublic')->will($this->returnValue(true));
        $defMock->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Egulias\ListenersDebug\Tests\Listener\Listener'));

        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock->expects($this->once())->method('getDefinitions')
            ->will($this->returnValue(array($defMock)));

        $containerMock->expects($this->once())->method('findTaggedServiceIds')
            ->will($this->returnValue(array(array($eventData['config']))));

        $containerMock->expects($this->any())->method('hasDefinition')->will($this->returnValue(true));
        $containerMock->expects($this->once())->method('getDefinition')->will($this->returnValue($defMock));
        $fetcher = new ListenerFetcher($containerMock);
        $listeners = $fetcher->fetchListeners();

        $this->assertInstanceOf('Egulias\ListenersDebug\Listener\Collection', $listeners);
        $this->assertInstanceOf('Egulias\ListenersDebug\Listener\Listener', $listeners[0]);
        $this->assertEquals($eventData['expectations']['event'], $listeners[0]->event);
        $this->assertEquals($eventData['expectations']['type'], $listeners[0]->type);
        $this->assertEquals($eventData['expectations']['priority'], $listeners[0]->priority);
    }

    public function testDefinitionIsSubscriber()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $defMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $defMock->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue('Egulias\ListenersDebug\Tests\Listener\Listener'));
        $fetcher = new ListenerFetcher($containerMock);
        $this->assertTrue($fetcher->isSubscriber($defMock));
    }

    public function testDefinitionIsNotSubscriber()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $defMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $defMock->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue('Egulias\ListenersDebug\Listener\ListenerFetcher'));
        $fetcher = new ListenerFetcher($containerMock);
        $this->assertFalse($fetcher->isSubscriber($defMock));
    }

    public function eventListenerDataProvider()
    {
        return array (
            array(
                array(
                    'name' => 'test0.event_listener',
                    'config' => array(
                        'event' => 'test.event', 'method' => 'onTestEvent', 'priority' => 4
                    ),
                    'expectations' => array(
                        'priority' => 4,
                        'type' => 'listener',
                        'event' => 'test.event',
                        'method' => 'onTestEvent'
                    )
                )
            ),
            array(
                array(
                    'name' => 'test1.event_listener',
                    'config' => array(
                        'event' => 'test.event', 'method' => 'onTestEvent'
                    ),
                    'expectations' => array(
                        'priority' => 0,
                        'type' => 'listener',
                        'event' => 'test.event',
                        'method' => 'onTestEvent'
                    )
                )
            ),
            array(
                array(
                    'name' => 'test1.event_listener',
                    'config' => array(
                        'event' => 'test.event',
                    ),
                    'expectations' => array(
                        'priority' => 0,
                        'type' => 'listener',
                        'event' => 'test.event',
                        'method' => ''
                    )
                )
            ),
            array(
                array(
                    'name' => 'test1.event_listener',
                    'config' => array(
                        'event' => 'test.event', 'priority' => 2
                    ),
                    'expectations' => array(
                        'priority' => 2,
                        'type' => 'listener',
                        'event' => 'test.event',
                        'method' => ''
                    )
                )
            ),
            array(
                array(
                    'name' => 'test1.event_listener',
                    'config' => array('test.event'),
                    'expectations' => array(
                        'priority' => -2,
                        'type' => 'subscriber',
                        'event' => 'test.event',
                        'method' => ''
                    )
                )
            ),
        );
    }
}
