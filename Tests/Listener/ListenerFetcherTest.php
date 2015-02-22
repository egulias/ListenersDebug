<?php

/**
 * This file is part of ListenersDebug
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egulias\ListenersDebug\Tests\Listener;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use Egulias\ListenersDebug\Listener\ListenerFetcher;


/**
 * ListenerFetcherTest
 *
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class ListenerFetcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider eventListenerDataProvider
     */
    public function testFetchListeners($eventData)
    {
        $defMock = m::mock('Symfony\Component\DependencyInjection\Definition');
        $defMock->shouldReceive('getTags')->once()->andReturn(array($eventData['name'] => array()));
        $defMock->shouldReceive('isPublic')->once()->andReturn(true);
        $defMock->shouldReceive('getClass')->andReturn('Egulias\ListenersDebug\Tests\Listener\Listener');

        $containerMock = m::mock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $containerMock->shouldReceive('getDefinitions')->once()->andReturn(array($defMock));
        $containerMock->shouldReceive('findTaggedServiceIds')->once()->andReturn(array(array($eventData['config'])));
        $containerMock->shouldReceive('hasDefinition')->andReturn(true);
        $containerMock->shouldReceive('getDefinition')->once()->andReturn($defMock);

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
