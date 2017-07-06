<?php

namespace TweedeGolf\PrometheusClient\Collector;

use TweedeGolf\PrometheusClient\PrometheusException;
use TweedeGolf\PrometheusClient\MetricFamilySamples;
use TweedeGolf\PrometheusClient\Sample;
use TweedeGolf\PrometheusClient\Storage\StorageAdapterInterface;
use TweedeGolf\PrometheusClient\Validator;

class Counter implements CollectorInterface
{
    const TYPE = 'counter';

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
     * @var string|null
     */
    private $help;

    /**
     * @param StorageAdapterInterface $storage
     * @param string[]|string $name
     * @param string[] $labelNames
     * @param string|null $help
     * @throws PrometheusException
     */
    public function __construct(StorageAdapterInterface $storage, $name, array $labelNames = [], $help = null)
    {
        Validator::validateAllLabels($labelNames);
        $this->name = Validator::makeName($name);
        $this->help = $help;
        $this->storage = $storage;
        $this->labelNames = $labelNames;
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
        if ($v < 0) {
            throw new PrometheusException("Increment must be positive");
        }
        Validator::validateLabelValues($labelValues, $this->labelNames);
        $this->storage->incValue($this->name, $v, is_int($v) ? 0 : 0.0, $this->labelNames);

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
