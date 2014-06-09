<?php

namespace Egulias\ListenersDebug\Listener;

use ArrayObject;
use InvalidArgumentException;

class Collection extends ArrayObject
{
    private $listeners = array();

    public function append($listener)
    {
        if (!is_array($listener)) {
            throw new InvalidArgumentException();
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
 