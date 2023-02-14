<?php

namespace TweedeGolf\PrometheusClient\Storage;

class ApcuAdapter implements StorageAdapterInterface
{
    use AdapterTrait;

    const MAX_INC_TRIES = 10;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param string $prefix
     */
    public function __construct($prefix = StorageAdapterInterface::DEFAULT_KEY_PREFIX)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    protected function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @inheritDoc
     */
    public function setValue($key, $value, array $labelValues)
    {
        \apcu_add($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelValues);
        return \apcu_store($this->getKey($key, $labelValues), $value);
    }

    /**
     * @inheritDoc
     */
    public function incValue($key, $inc, $default, array $labelValues)
    {
        $storeKey = $this->getKey($key, $labelValues);

        if (!is_callable($default)) {
            $value = $default;
            $default = function () use ($value) { return $value; };
        }
        \apcu_entry($storeKey, $default);
        \apcu_add($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelValues);

        if (is_float($inc)) {
            // unfortunately we can only non-atomically update this value
            $currentValue = \apcu_fetch($storeKey, $retrievedCurrent);
            if (!$retrievedCurrent) {
                return false;
            }
            return $this->setValue($key, $currentValue + $inc, $labelValues);
        } else {
            \apcu_inc($storeKey, $inc, $success);
            return $success;
        }
    }

    /**
     * @inheritDoc
     */
    public function getValues($key)
    {
        $escapedKey = preg_quote("{$this->prefix}||{$key}|", '/');
        $iterator = new \APCUIterator("/^{$escapedKey}.*$/");
        $items = [];
        foreach ($iterator as $entry) {
            list(,,$key,$extra) = explode('|', $entry['key'], 4);
            $labelValuesKey = "{$this->prefix}|".StorageAdapterInterface::LABEL_PREFIX."|{$key}|{$extra}";
            $labels = \apcu_fetch($labelValuesKey, $fetchedLabels);
            if (!$fetchedLabels) {
                continue;
            }
            $value = $entry['value'];
            $items[] = [$value, $labels];
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getValue($key, array $labelValues)
    {
        $value = \apcu_fetch($this->getKey($key, $labelValues), $valueRetrieved);
        $labels = \apcu_fetch($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelsRetrieved);

        if (!$valueRetrieved || !$labelsRetrieved) {
            return null;
        }

        return [$value, $labels];
    }

    /**
     * @inheritDoc
     */
    public function hasValue($key, array $labelValues)
    {
        return \apcu_exists($this->getKey($key, $labelValues));
    }
}
