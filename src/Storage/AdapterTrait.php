<?php

namespace TweedeGolf\PrometheusClient\Storage;

trait AdapterTrait
{
    abstract protected function getPrefix();

    protected function getLabelHash(array $labelValues)
    {
        return hash('crc32', serialize($labelValues));
    }

    /**
     * Make the base retrieval key for some set of labels and a specific metric key
     *
     * @param string $key
     * @param string[]|string $labelValues
     * @param string $prefix
     * @return string
     */
    protected function getKey($key, $labelValues, $prefix = '')
    {
        if (is_array($labelValues)) {
            $labelHash = $this->getLabelHash($labelValues);
        } else {
            $labelHash = $labelValues;
        }
        return $this->getPrefix().'|'.$prefix.'|'.$key.'|'.$labelHash;
    }
}
