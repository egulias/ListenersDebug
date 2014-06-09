<?php

namespace Egulias\ListenersDebug\Listener;

use ArrayObject;
use InvalidArgumentException;

class Collection extends ArrayObject
{
    private $listeners = array();

    public function append($listener)
    {
        if (!$listener instanceof Listener) {
            throw new InvalidArgumentException(get_class($listener));
        }

       $this->listeners[] = $listener;
    }

    public function appendMany(array $listeners)
    {
        foreach($listeners as $listener) {
            $this->append($listener);
        }
    }

    public function offsetGet($index)
    {
        return $this->listeners[$index];
    }
}
 