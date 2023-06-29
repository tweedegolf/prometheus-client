# Not maintained

Since Tweede Golf does not use / maintain this library anymore (for several years already) - this repository is archived.
If you like like to maintain this - please contact us at support@tweedegolf.com

# Tweede Golf Prometheus PHP Client
A PHP Client for prometheus, providing several storage backends. This client 
mostly follows the guidelines as specified in the [prometheus docs].

This library currently does not implement the Summary metric type.
For users of Symfony a [prometheus client bundle] is available.

## Installation
This library uses [Composer]. Simply run the following command to add it as
a dependency to your project:

    composer require tweedegolf/prometheus-client

## Usage
To start, you must create a `CollectorRegistry`. To this registry you may
register any number of metric collectors. To create a collector registry you
must specificy a storage adapter. For easy setup you may want to try the APC
or APCU Storage adapters. See the example below:

```php
use TweedeGolf\PrometheusClient\CollectorRegistry;
use TweedeGolf\PrometheusClient\Storage\ApcuAdapter;

$registry = new CollectorRegistry(new ApcuAdapter());
$registry->createCounter('requests', [], null, true);
$registry->createGauge('traffic', ['endpoint'], 'Active traffic per endpoint', true);
```

Next on some event (like a request entering your application) you can modify 
the existing metrics. An example is shown below:

```php
$registry->getCounter('requests')->inc();
$registry->getGauge('traffic')->set(10, ['/home']);
```

Finally your application should expose some endpoint where metrics can be 
scraped by Prometheus:

```php
use TweedeGolf\PrometheusClient\Format\TextFormatter;

$formatter = new TextFormatter();
header('Content-Type', $formatter->getMimeType());
echo $formatter->format($registry->collect());
```

[prometheus docs]: https://prometheus.io/docs/instrumenting/clientlibs/
[Composer]: https://getcomposer.org
[prometheus client bundle]: https://github.com/tweedegolf/prometheus-bundle
