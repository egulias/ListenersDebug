# Listeners debug for Symfony2 DIC Events [![Build Status](https://travis-ci.org/egulias/ListenersDebug.png?branch=master)](https://travis-ci.org/egulias/ListenersDebug) [![Coverage Status](https://coveralls.io/repos/egulias/ListenersDebug/badge.png?branch=master)](https://coveralls.io/r/egulias/ListenersDebug?branch=master)

This library will fetch information about all the listeners tagged with .event_listener inside the DIC

# Installation and configuration

## Get the lib
php composer.phar require egulias/listeners-debug

## Use
Basic usage
-----------

```php
<?php

use Egulias\ListenersDebug\ListenerFetcher;

$fetcher = new ListenerFetcher($containerBuilder);

$listeners = $fetcher->fetchListeners($showPrivate);
$listener = $fetcher->fetchListener($listenerServiceId);
```

Filtering
-----------

```php
<?php

use Egulias\ListenersDebug\ListenerFetcher;
use Egulias\ListenersDebug\ListenerFilter;

$fetcher = new ListenerFetcher($containerBuilder);
$filter = new ListenerFilter();

$listeners = $fetcher->fetchListeners($showPrivate);

$filteredAndOrdered = $filter->filterByEvent($eventName, $listeners, $orderByPriorityAsc);
```

