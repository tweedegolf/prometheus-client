<?php

namespace TweedeGolf\PrometheusClient\Collector;

use TweedeGolf\PrometheusClient\PrometheusException;
use TweedeGolf\PrometheusClient\Validator;
use TweedeGolf\PrometheusClient\Storage\StorageAdapterInterface;

class Histogram implements CollectorInterface
{
    const TYPE = 'histogram';

    const DEFAULT_BUCKETS = [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0];

    const COUNT_POSTFIX = '_count';
    const SUM_POSTFIX = '_sum';

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
     * @var float[]
     */
    private $buckets;

    /**
     * @var string|null
     */
    private $help;

    /**
     * @param StorageAdapterInterface $storage
     * @param string[]|string $name
     * @param string[] $labelNames
     * @param float[]|null $buckets
     * @param string|null $help
     * @throws PrometheusException
     */
    public function __construct(StorageAdapterInterface $storage, $name, array $labelNames = [], $buckets = null, $help = null)
    {
        Validator::validateAllLabels($labelNames, ['le']);

        $this->storage = $storage;
        $this->name = Validator::makeName($name);
        $this->help = $help;
        $this->labelNames = $labelNames;

        if (!is_array($buckets)) {
            $buckets = self::DEFAULT_BUCKETS;
        }

        for ($i = 1; $i < count($buckets); $i++) {
            if ($buckets[$i - 1] >= $buckets[$i]) {
                throw new PrometheusException("Buckets must always be larger than the previous one");
            }
        }
        $this->buckets = $buckets;
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
    public function observe($v, array $labelValues = [])
    {
        Validator::validateLabelValues($labelValues, $this->labelNames);
        $this->storage->incValue($this->name.self::COUNT_POSTFIX, 1, 0, $labelValues);
        $this->storage->incValue($this->name.self::SUM_POSTFIX, $v, is_int($v) ? 0 : 0.0, $labelValues);
        foreach ($this->buckets as $bucketMax) {
            if ($v <= $bucketMax) {
                $this->storage->incValue($this->name, 1, 0, array_merge([$bucketMax], $labelValues));
            }
        }
        $this->storage->incValue($this->name, 1, 0, array_merge(['+Inf'], $labelValues));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function collect()
    {
        // TODO: Implement collect() method.
    }
}
