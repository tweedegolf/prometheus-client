<?php

namespace TweedeGolf\PrometheusClient\Storage;

class RedisAdapter implements StorageAdapterInterface
{
    use AdapterTrait;

    private $prefix;

    private $redis;

    public function __construct(\Redis $redis, $prefix = StorageAdapterInterface::DEFAULT_KEY_PREFIX)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    protected function getPrefix()
    {
        return $this->prefix;
    }

    public function getValues($key)
    {
        $items = [];
        foreach ($this->redis->sMembers($this->getSetName($key)) as $entry) {
            // get the value
            $value = $this->redis->get($entry);
            $value = $value === false ? null : floatval($value);

            // get the labels
            list(,,$key,$extra) = explode('|', $entry, 4);
            $labelValuesKey = "{$this->prefix}|".StorageAdapterInterface::LABEL_PREFIX."|{$key}|{$extra}";
            $labelData = $this->redis->get($labelValuesKey);

            // check label data
            if (is_string($labelData) && strlen($labelData) > 0) {
                $labels = json_decode($labelData, true);
            } else {
                $labels = [];
            }
            if (!is_array($labels)) {
                $labels = [];
            }
            $items[] = [$value, $labels];
        }

        return $items;
    }

    public function getValue($key, array $labelValues)
    {
        $this->redis->setNx($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), json_encode($labelValues));

        $val = $this->redis->get($this->getKey($key, $labelValues));
        if ($val === false) {
            return null;
        }

        return floatval($val);
    }

    public function setValue($key, $value, array $labelValues)
    {
        $this->redis->setNx($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), json_encode($labelValues));

        $fullKey = $this->getKey($key, $labelValues);
        $this->redis->sAdd($this->getSetName($key), $fullKey);
        $this->redis->set($fullKey, $value);
    }

    public function incValue($key, $inc, $default, array $labelValues)
    {
        $this->redis->setNx($this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX), json_encode($labelValues));

        $stored = false;
        $tries = 0;
        $fullKey = $this->getKey($key, $labelValues);
        $this->redis->sAdd($this->getSetName($key), $fullKey);
        while ($stored === false) {
            $this->redis->watch($fullKey);
            if (!$this->redis->exists($fullKey)) {
                $stored = $this->redis->multi()
                    ->set($fullKey, is_callable($default) ? $default() : $default)
                    ->incrByFloat($fullKey, $inc)
                    ->exec();
            } else {
                $stored = $this->redis->incrByFloat($fullKey, $inc);
            }
        }
    }

    public function hasValue($key, array $labelValues)
    {
        return $this->redis->exists($this->getKey($key, $labelValues));
    }

    private function getSetName($key)
    {
        return $this->getPrefix().'|'.$key;
    }
}
