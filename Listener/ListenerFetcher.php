<?php

namespace Egulias\ListenersDebug\Listener;

use Egulias\ListenersDebug\Listener\Collection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

class ListenerFetcher
{
    const LISTENER_PATTERN = '/.+\.event_listener/';
    const SUBSCRIBER_PATTERN = '/.+\.event_subscriber/';

    protected $listeners = array();
    protected $listenersList;
    protected $builder;

    public function __construct(ContainerBuilder $builder)
    {
        $this->builder = $builder;
        $this->listenersList = new Collection();
    }

    public function fetchListeners($showPrivate = false)
    {
        $listenersIds = $this->getIds();

        foreach ($listenersIds as $serviceId) {
            $definition = $this->resolveServiceDef($serviceId);
            if (!$showPrivate && !$definition->isPublic()) {
                continue;
            }

            if ($definition instanceof Definition) {
                foreach ($this->listeners[$serviceId]['tag'] as $listener) {
                    //this is probably an EventSubscriber
                    if (!isset($listener['event'])) {
                        $this->appendSubscribedEvents($definition, $serviceId);
                        continue;
                    }

                    $listener = array(
                        $serviceId,
                        $listener['event'],
                        $this->getListenerMethod($listener),
                        $this->getListenerPriority($listener),
                        'listener',
                        $definition->getClass()
                    );
                    $this->listenersList->append($listener);
                }
            } elseif ($definition instanceof Alias) {
                $listener = array(
                    $serviceId,
                    'n/a',
                    0,
                    sprintf('<comment>alias for</comment> <info>%s</info>', (string) $definition),
                    $definition->getClass()
                );
                $this->listenersList->append($listener);
            }
        }
        return $this->listenersList;

    }

    public function fetchListener($serviceId)
    {
        return $this->resolveServiceDef($serviceId);
    }

    public function isSubscriber(Definition $definition)
    {
        return ($this->classIsEventSubscriber($definition->getClass())) ? true  : false;
    }

    /**
     * Obtains the information available from class if it is defined as an EventSubscriber
     *
     * @param string $class Fully qualified class name
     *
     * @return array array('event.name' => array(array('method','priority')))
     */
    public function getEventSubscriberInformation($class)
    {
        $events = array();
        $reflectionClass = new \ReflectionClass($class);
        $interfaces = $reflectionClass->getInterfaceNames();
        foreach ($interfaces as $interface) {
            if ($interface == 'Symfony\\Component\\EventDispatcher\\EventSubscriberInterface') {
                return $class::getSubscribedEvents();
            }
        }

        return $events;
    }

    protected function getListenerMethod(array $listenerInfo)
    {
        $method = '';
        if (isset($listenerInfo['method'])) {
            $method = $listenerInfo['method'];
        }

        return $method;
    }

    protected function getListenerPriority(array $listenerInfo)
    {
        $priority = 0;
        if (isset($listenerInfo['priority'])) {
            $priority = $listenerInfo['priority'];
        }

        return $priority;
    }

    protected function getIds()
    {
        $listenersIds = array();
        if (!$this->hasEventDispatcher()) {
            return $listenersIds;
        }

        $dfs = $this->builder->getDefinitions();

        foreach ($dfs as $v) {
            $tags = $v->getTags();
            if (empty($tags)) {
                continue;
            }
            $keys = array_keys($tags);
            if (preg_match(self::LISTENER_PATTERN, $keys[0]) || preg_match(self::SUBSCRIBER_PATTERN, $keys[0])) {
                $fullTags[$keys[0]] = $keys[0];
            }
        }

        foreach ($fullTags as $tag) {
            $services = $this->builder->findTaggedServiceIds($tag);
            foreach ($services as $id => $events) {
                $this->listeners[$id]['tag'] = $events;
                $listenersIds[$id] = $id;
            }
        }
        asort($listenersIds);

        return $listenersIds;
    }

    /**
     * @return bool
     */
    protected function hasEventDispatcher()
    {
        return (
            $this->builder->hasDefinition('debug.event_dispatcher') ||
            $this->builder->hasDefinition('event_dispatcher')
        );
    }

    /**
     * @param string           $serviceId
     * @return mixed
     */
    protected function resolveServiceDef($serviceId)
    {
        if ($this->builder->hasDefinition($serviceId)) {
            return $this->builder->getDefinition($serviceId);
        }

        if ($this->builder->hasAlias($serviceId)) {
            return $this->builder->getAlias($serviceId);
        }

        return $this->builder->get($serviceId);
    }

    protected function appendSubscribedEvents(Definition $definition, $serviceId)
    {
        $events = $this->getEventSubscriberInformation($definition->getClass());
        foreach ($events as $name => $event) {
            if (is_array($event) && is_array($event[0])) {
                $subscribed = $this->fetchFromMultipleSubscribedListeners($event, $definition->getClass(), $serviceId, $name);
                $this->listenersList->appendMany($subscribed);

                continue;
            }

            $priority = $this->getSubscribedListenerPriority($event);

            if (!is_array($event)) {
                $event = array($event);
            }

            $listener = array(
                $serviceId,
                $name,
                $event[0],
                $priority,
                'subscriber',
                $definition->getClass()
            );

            $this->listenersList->append($listener);
        }
    }

    protected function fetchFromMultipleSubscribedListeners(array $subscribedEvents, $class, $serviceId, $eventName)
    {
        $listenersList = array();
        foreach ($subscribedEvents as $property) {
            $method = $property[0];
            $priority = $this->getSubscribedListenerPriority($property);

            $listenersList[] = array(
                $serviceId,
                $eventName,
                $method,
                $priority,
                'subscriber',
                $class
            );
        }

        return $listenersList;
    }

    protected function getSubscribedListenerPriority($property)
    {
        $priority = 0;
        if (is_array($property) && isset($property[1]) && is_int($property[1])) {
            $priority = $property[1];
        }

        return $priority;
    }
}
