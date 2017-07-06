<?php

namespace TweedeGolf\PrometheusClient\Storage;

interface StorageAdapterInterface
{
    const DEFAULT_KEY_PREFIX = 'tweede_golf_prometheus';

    const LABEL_PREFIX = '__labels';

    /**
     * @param string $key
     *
     * @return array[]
     */
    public function getValues($key);

    /**
     * @param string $key
     * @param string[] $labelValues
     *
     * @return array
     */
    public function getValue($key, array $labelValues);

    /**
     * @param string $key
     * @param float $value
     * @param string[] $labelValues
     *
     * @return bool
     */
    public function setValue($key, $value, array $labelValues);

    /**
     * @param string $key
     * @param float $inc
     * @param callable|float $default
     * @param string[] $labelValues
     *
     * @return bool
     */
    public function incValue($key, $inc, $default, array $labelValues);

    /**
     * @param string $key
     * @param string[] $labelValues
     *
     * @return bool
     */
    public function hasValue($key, array $labelValues);
}
