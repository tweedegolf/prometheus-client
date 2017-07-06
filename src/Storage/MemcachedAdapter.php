<?php

namespace TweedeGolf\PrometheusClient\Storage;

class MemcachedAdapter implements StorageAdapterInterface
{
    use AdapterTrait;

    const MAX_INC_TRIES = 10;

    const KNOWN_LABELS_PREFIX = '__known_labels';

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var \Memcached
     */
    private $pool;

    /**
     * @param \Memcached $pool
     * @param string $prefix
     */
    public function __construct(\Memcached $pool, $prefix = StorageAdapterInterface::DEFAULT_KEY_PREFIX)
    {
        $this->prefix = $prefix;
        $this->pool = $pool;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @inheritDoc
     */
    public function getValues($key)
    {
        $knownLabelsKey = $this->getKey($key, [], self::KNOWN_LABELS_PREFIX);
        $knownLabels = $this->pool->get($knownLabelsKey);
        $knownLabelsRetrieved = $this->pool->getResultCode() === \Memcached::RES_SUCCESS;
        if (!$knownLabelsRetrieved) {
            return [];
        }

        $items = [];
        foreach ($knownLabels as $labelHash) {
            $value = $this->getKey($key, $labelHash);
            $valueRetrieved = $this->pool->getResultCode() === \Memcached::RES_SUCCESS;
            $labels = $this->getKey($key, $labelHash, StorageAdapterInterface::LABEL_PREFIX);
            $labelsRetrieved = $this->pool->getResultCode() === \Memcached::RES_SUCCESS;
            if ($valueRetrieved && $labelsRetrieved) {
                $items[] = [$value, $labels];
            }
        }

        return $items;
    }

    /**
     * @param string $key
     * @param string[] $labelValues
     * @return bool
     */
    private function addKnownLabels($key, array $labelValues)
    {
        $knownLabelsKey = $this->getKey($key, [], self::KNOWN_LABELS_PREFIX);
        $labelHash = $this->getLabelHash($labelValues);
        $this->pool->add($knownLabelsKey, [$labelHash]);
        $success = false;
        for ($tries = 0; $success === false && $tries < self::MAX_INC_TRIES; $tries++) {
            $current = $this->pool->get($knownLabelsKey, null, $casToken);
            if (!in_array($labelHash, $current, true)) {
                $success = $this->pool->cas($casToken, $knownLabelsKey, array_merge($current, [$labelHash]));
            } else {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function getValue($key, array $labelValues)
    {
        $valueKey = $this->getKey($key, $labelValues);
        $labelKey = $this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX);

        $value = $this->pool->get($valueKey);
        $valueRetrieved = $this->pool->getResultCode() === \Memcached::RES_SUCCESS;

        $labels = $this->pool->get($labelKey);
        $labelsRetrieved = $this->pool->getResultCode() === \Memcached::RES_SUCCESS;

        if ($valueRetrieved && $labelsRetrieved) {
            return [$value, $labels];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function setValue($key, $value, array $labelValues)
    {
        $this->pool->add($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelValues);
        $result = $this->pool->set($this->getKey($key, $labelValues), $value);
        $this->addKnownLabels($key, $labelValues);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function incValue($key, $inc, $default, array $labelValues)
    {
        $storeKey = $this->getKey($key, $labelValues);

        $this->pool->add($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), $labelValues);
        if (!$this->hasValue($key, $labelValues)) {
            $this->pool->set($storeKey, is_callable($default) ? $default() : $default);
        }

        if (is_int($inc)) {
            $success = false !== $this->pool->increment($storeKey, $inc);
        } else {
            $success = false;
            for ($tries = 0; $success === false && $tries < self::MAX_INC_TRIES; $tries++) {
                $current = $this->pool->get($storeKey, null, $token);
                $success = $this->pool->cas($token, $storeKey, $current + $inc);
            }
        }
        $this->addKnownLabels($key, $labelValues);

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function hasValue($key, array $labelValues)
    {
        $result = $this->pool->get($this->getKey($key, $labelValues));
        return false !== $result || $this->pool->getResultCode() !== \Memcached::RES_NOTFOUND;
    }
}
