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

        $this->assertCount(6, $listeners[0]);
        $this->assertEquals($eventData['expectations']['event'], $listeners[0][1]);
        $this->assertEquals($eventData['expectations']['type'], $listeners[0][4]);
        $this->assertEquals($eventData['expectations']['priority'], $listeners[0][3]);
        $this->assertEquals('Egulias\ListenersDebug\Tests\Listener\Listener', $listeners[0][5]);

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
