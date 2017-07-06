<?php

namespace TweedeGolf\PrometheusClient\Storage;

class ApcAdapter implements StorageAdapterInterface
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
        \apc_add($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelValues);
        return \apc_store($this->getKey($key, $labelValues), $value);
    }

    /**
     * @inheritDoc
     */
    public function incValue($key, $inc, $default, array $labelValues)
    {
        $storeKey = $this->getKey($key, $labelValues);
        if (!\apc_exists($storeKey)) {
            \apc_add($storeKey, is_callable($default) ? $default() : $default);
        }
        \apc_add($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelValues);

        if (is_float($inc)) {
            // unfortunately we can only non-atomically update this value
            $currentValue = \apc_fetch($storeKey, $retrievedCurrent);
            if (!$retrievedCurrent) {
                return false;
            }
            return $this->setValue($key, $currentValue + $inc, $labelValues);
        } else {
            \apc_inc($storeKey, $inc, $success);
            return $success;
        }
    }

    /**
     * @inheritDoc
     */
    public function getValues($key)
    {
        $escapedKey = preg_quote("{$this->prefix}||${key}|", '/');
        $iterator = new \APCIterator('user', "/^{$escapedKey}.*$/");
        $items = [];
        foreach ($iterator as $entry) {
            list(,,$key,$extra) = explode('|', $entry['key'], 4);
            $labelValuesKey = "{$this->prefix}|".StorageAdapterInterface::LABEL_PREFIX."|{$key}|{$extra}";
            $labels = \apc_fetch($labelValuesKey, $fetchedLabels);
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
        $value = \apc_fetch($this->getKey($key, $labelValues), $valueRetrieved);
        $labels = \apc_fetch($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelsRetrieved);

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
        return \apc_exists($this->getKey($key, $labelValues));
    }
}
