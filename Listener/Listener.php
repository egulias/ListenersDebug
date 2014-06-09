<?php

namespace Egulias\ListenersDebug\Listener;

class Listener
{
    const TYPE_LISTENER = 'listener';
    const TYPE_SUBSCRIBER = 'subscriber';

    public $event = '';
    public $method = '';
    public $priority = 0;
    public $service = '';
    public $class = '';
    public $public = true;
    public $type = self::TYPE_LISTENER;
}
 