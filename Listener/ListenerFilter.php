<?php

/**
 * This file is part of ListenersDebug
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egulias\ListenersDebug\Listener;

/**
 * ListenerFilter
 *
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class ListenerFilter
{
    public function filterByEvent($event, $listeners, $asc = false)
    {
        $listenersList = array_filter(
            $listeners,
            function ($listener) use ($event) {
                return $listener[1] === $event;
            }
        );

        if ($asc) {
            usort(
                $listenersList,
                function ($a, $b) use ($asc) {
                    if ($asc) {
                        return ($a[3] >= $b[3]) ? 1 : -1;
                    }

                    return ($a[3] <= $b[3]) ? 1 : -1;
                }
            );
        }

        return $listenersList;
    }

    public function getListeners($listeners)
    {
        return array_filter(
            $listeners,
            function ($listener) {
                return $listener[4] === 'listener';
            }
        );
    }

    public function getSubscribers($listeners)
    {
        return array_filter(
            $listeners,
            function ($listener) {
                return $listener[4] === 'subscriber';
            }
        );
    }
}
