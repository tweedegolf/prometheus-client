<?php

namespace TweedeGolf\PrometheusClient\Collector;

use TweedeGolf\PrometheusClient\PrometheusException;
use TweedeGolf\PrometheusClient\MetricFamilySamples;
use TweedeGolf\PrometheusClient\Sample;
use TweedeGolf\PrometheusClient\Storage\StorageAdapterInterface;
use TweedeGolf\PrometheusClient\Validator;

class Gauge implements CollectorInterface
{
    const TYPE = 'gauge';

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $labelNames;

    /**
     * @var callable|null
     */
    private $initializer;

    /**
     * @var string|null
     */
    private $help;

    /**
     * @param StorageAdapterInterface $storage
     * @param string[]|string $name
     * @param string[] $labelNames
     * @param callable|mixed|null $initializer
     * @param string|null $help
     * @throws PrometheusException
     */
    public function __construct(StorageAdapterInterface $storage, $name, array $labelNames = [], $initializer = null, $help = null)
    {
        Validator::validateAllLabels($labelNames);
        $this->storage = $storage;
        $this->name = Validator::makeName($name);
        $this->help = $help;
        $this->labelNames = $labelNames;

        if (null !== $initializer && !is_callable($initializer)) {
            $value = $initializer;
            $initializer = function () use ($value) { return $value; };
        }
        $this->initializer = $initializer;
    }

    /**
     * @inheritDoc
     */
    public function getLabelNames()
    {
        return $this->labelNames;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param int|float $v
     * @param mixed[] $labelValues
     * @throws PrometheusException
     * @return $this
     */
    public function inc($v = 1, array $labelValues = [])
    {
        Validator::validateLabelValues($labelValues, $this->labelNames);
        $default = function () use ($labelValues) {
            if (is_callable($this->initializer)) {
                $callable = $this->initializer;
                return $callable($this->labelNames, $labelValues, $this->name);
            } else {
                return 0;
            }
        };
        $this->storage->incValue($this->name, $v, $default, $this->labelNames);

        return $this;
    }

    /**
     * @param int|float $v
     * @param mixed[] $labelValues
     * @throws PrometheusException
     * @return $this
     */
    public function dec($v = 1, array $labelValues = [])
    {
        return $this->inc(-$v, $labelValues);
    }

    /**
     * @param int|float $v
     * @param mixed[] $labelValues
     * @throws PrometheusException
     * @return $this
     */
    public function set($v, array $labelValues = [])
    {
        Validator::validateLabelValues($labelValues, $this->labelNames);
        $this->storage->setValue($this->name, $v, $labelValues);
        return $this;
    }

    /**
     * Sets the value of the gauge to the current UTC timestamp
     *
     * @param mixed[] $labelValues
     * @throws PrometheusException
     * @return $this
     */
    public function setToCurrentTime(array $labelValues = [])
    {
        $this->set(time(), $labelValues);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function collect()
    {
        $data = $this->storage->getValues($this->name);
        $samples = [];
        foreach ($data as $sample) {
            $samples[] = new Sample($this->name, $this->labelNames, $sample[1], $sample[0]);
        }

        if (count($samples) === 0 && count($this->labelNames) === 0) {
            $samples[] = new Sample($this->name, [], [], 0);
        }

        if (count($samples) === 0) {
            return [];
        }

        return [
            new MetricFamilySamples($this->name, $this->getType(), $this->getHelp(), $samples)
        ];
    }
}
