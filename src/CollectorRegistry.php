<?php

namespace TweedeGolf\PrometheusClient;

use TweedeGolf\PrometheusClient\Collector\CollectorInterface;
use TweedeGolf\PrometheusClient\Collector\Counter;
use TweedeGolf\PrometheusClient\Collector\Gauge;
use TweedeGolf\PrometheusClient\Collector\Histogram;
use TweedeGolf\PrometheusClient\Storage\StorageAdapterInterface;

class CollectorRegistry
{
    /**
     * @var CollectorInterface[]
     */
    private $collectors = [];

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    public function __construct(StorageAdapterInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param CollectorInterface $collector
     * @return $this
     */
    public function register(CollectorInterface $collector)
    {
        if (!in_array($collector, $this->collectors, true)) {
            $this->collectors[] = $collector;
        }

        return $this;
    }

    /**
     * @param string[]|string $name
     * @throws PrometheusException
     * @return CollectorInterface
     */
    public function get($name)
    {
        $name = Validator::makeName($name);

        foreach ($this->collectors as $collector) {
            if ($collector->getName() === $name) {
                return $collector;
            }
        }

        throw new PrometheusException("No collector with name '{$name}' registered");
    }

    /**
     * @param string $name
     * @return Counter
     * @throws PrometheusException
     */
    public function getCounter($name)
    {
        $collector = $this->get($name);
        if ($collector instanceof Counter) {
            return $collector;
        }
        throw new PrometheusException("A collector with name '{$name}' was found, but it is not a counter");
    }

    /**
     * @param string $name
     * @return Gauge
     * @throws PrometheusException
     */
    public function getGauge($name)
    {
        $collector = $this->get($name);
        if ($collector instanceof Gauge) {
            return $collector;
        }
        throw new PrometheusException("A collector with name '{$name}' was found, but it is not a gauge");
    }

    /**
     * @param string $name
     * @return Histogram
     * @throws PrometheusException
     */
    public function getHistogram($name)
    {
        $collector = $this->get($name);
        if ($collector instanceof Histogram) {
            return $collector;
        }
        throw new PrometheusException("A collector with name '{$name}' was found, but it is not a histogram");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        try {
            $name = Validator::makeName($name);
        } catch (PrometheusException $e) {
            return false;
        }

        foreach ($this->collectors as $collector) {
            if ($collector->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CollectorInterface $collector
     * @return $this
     */
    public function unregister(CollectorInterface $collector)
    {
        for ($i = count($this->collectors) - 1; $i >= 0; $i--) {
            if ($this->collectors[$i] === $collector) {
                array_splice($this->collectors, $i, 1);
            }
        }

        return $this;
    }

    /**
     * @param string[]|string $name
     * @param string[] $labelNames
     * @param string|null $help
     * @param bool $register
     * @return Counter
     */
    public function createCounter($name, array $labelNames = [], $help = null, $register = false)
    {
        $collector = new Counter($this->storage, $name, $labelNames, $help);
        if ($register) {
            $this->register($collector);
        }

        return $collector;
    }

    /**
     * @param string[]|string $name
     * @param string[] $labelNames
     * @param callable|mixed|null $initializer
     * @param string|null $help
     * @param bool $register
     * @return Gauge
     */
    public function createGauge($name, array $labelNames = [], $initializer = null, $help = null, $register = false)
    {
        $collector = new Gauge($this->storage, $name, $labelNames, $initializer, $help);
        if ($register) {
            $this->register($collector);
        }

        return $collector;
    }

    /**
     * @param string[]|string $name
     * @param string[] $labelNames
     * @param float[]|null $buckets
     * @param string|null $help
     * @param bool $register
     * @return Histogram
     */
    public function createHistogram($name, array $labelNames = [], $buckets = null, $help = null, $register = false)
    {
        $collector = new Histogram($this->storage, $name, $labelNames, $buckets, $help);
        if ($register) {
            $this->register($collector);
        }

        return $collector;
    }

    /**
     * @return MetricFamilySamples[]
     */
    public function collect()
    {
        $samples = [];
        foreach ($this->collectors as $collector) {
            $samples = array_merge($samples, $collector->collect());
        }

        return $samples;
    }
}
