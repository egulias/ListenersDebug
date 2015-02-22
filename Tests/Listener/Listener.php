<?php

/**
 * This file is part of ListenersDebug
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egulias\ListenersDebug\Tests\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener
 *
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class Listener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'test.event' => array(
                array('onTestEvent', -2),
            ),
        );
    }
}
