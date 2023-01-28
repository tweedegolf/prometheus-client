<?php

namespace TweedeGolf\PrometheusClient\Storage;

class InMemoryAdapter implements StorageAdapterInterface
{
    use AdapterTrait;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $prefix
     */
    public function __construct($prefix = StorageAdapterInterface::DEFAULT_KEY_PREFIX)
    {
        $this->prefix = $prefix;
        $this->data = [];
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
        $this->data[$this->getKey($key, $labelValues)] = $value;
        $this->data[$this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX)] = $labelValues;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function incValue($key, $inc, $default, array $labelValues)
    {
        $storeKey = $this->getKey($key, $labelValues);

        if (!isset($this->data[$storeKey])) {
            $this->data[$storeKey] = is_callable($default) ? $default() : $default;
        }

        $this->data[$storeKey] = $this->data[$storeKey] + $inc;
        $this->data[$this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX)] = $labelValues;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getValues($key)
    {
        $escapedKey = preg_quote("{$this->prefix}||{$key}|", '/');
        $regex = "/^{$escapedKey}.*$/";
        $items = [];
        foreach ($this->data as $key => $value) {
            if (preg_match($regex, $key) === 1) {
                list(,,$name,$extra) = explode('|', $key, 4);
                $labelValuesKey = "{$this->prefix}|".StorageAdapterInterface::LABEL_PREFIX."|{$name}|{$extra}";
                if (isset($this->data[$labelValuesKey])) {
                    $labels = $this->data[$labelValuesKey];
                    $items[] = [$value, $labels];
                }
            }
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getValue($key, array $labelValues)
    {
        $valueKey = $this->getKey($key, $labelValues);
        $labelKey = $this->getKey($key, $labelValues, StorageAdapterInterface::LABEL_PREFIX);

        if (isset($this->data[$valueKey]) && isset($this->data[$labelKey])) {
            return [$this->data[$valueKey], $this->data[$labelKey]];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasValue($key, array $labelValues)
    {
        return isset($this->data[$this->getKey($key, $labelValues)]);
    }
}
