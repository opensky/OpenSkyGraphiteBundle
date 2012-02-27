# GraphiteBundle

This bundle integrates [graphite](https://launchpad.net/graphite) and [statsd](https://github.com/etsy/statsd) with Symfony2. It provides stats on all kernel event and controller execution times as well as providing  the ability to do any of the traditional graphite or statsd logging.

## Installation

### Submodule Creation

Add GraphiteBundle to your `src/` directory:

    $ git submodule add git://github.com/opensky/OpenSkyGraphiteBundle.git src/OpenSky/Bundle/GraphiteBundle

### Application Kernel

Add GraphiteBundle to the `registerBundles()` method of your application kernel:

```php
<?php
    public function registerBundles()
    {
        return array(
            new OpenSky\Bundle\GraphiteBundle\OpenSkyGraphiteBundle(),
        );
    }
```

Always log kernel startTime:

```php
<?php
    public function __construct($environment, $debug)
    {
        // always set start time
        $this->startTime = microtime(true);

        parent::__construct($environment, $debug);
    }
```

### Graphite Extension

GraphiteBundle allows you to log using either a udp connection, the symfony logger.

```yml
# app/config/config.yml

opensky_graphite:
    connection: udp # (null|udp|logging) defaults to null
    host: 127.0.0.1 # required for a udp connection
    port: 2003 # optional - defaults to 2003
    prefix: 'foo.' # optional - helps organize the data on your graphite server, automatically fills in statsd.prefix
    statsd:
        conenction: udp # (null|udp|logging) defaults to null
        host: 127.0.0.1 # required for a udp connection
        port: 8125 # defaults to 8125
        prefix: 'foo.' # optional - defaults to the graphite prefix
```
## Classes of note

### GraphiteEventDispatcher
Wraps the current `event_dispatcher` and logs the timing of each event dispatch. 

### GraphiteListener
* counts kernel exceptions by class name
* times the precontroller time of the master request
* times the duration of each controller call in the master and sub requests
* times the entire request time

### GraphiteLogger
Makes graphite calls. Expects a metric name and a numeric value. Defaults to the current time
```php
<?php
$graphiteLogger->log('my.metric', 42); // log a value of 42 now
```

### StatsDLogger
Makes statsd calls. Supports increment, decrement, and timing all with sampling rates as outlined in the statsd example 
```php
<?php
$statsdLogger->time('my.metric', 42); // indicate that it just took 42ms to run my.metric
$statsdLogger->increment('my.other.metric'); // my.other.metric's count just went up by one
$statsdLogger->decrement('my.other.metric'); // my.other.metric's count just went down by one
```