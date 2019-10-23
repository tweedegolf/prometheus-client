<?php

namespace TweedeGolf\PrometheusClient\Format;

use TweedeGolf\PrometheusClient\MetricFamilySamples;

interface FormatterInterface
{
    /**
     * @param MetricFamilySamples[] $metricFamilySamples
     * @return mixed
     */
    public function format(array $metricFamilySamples);

    /**
     * @return string
     */
    public function getMimeType();
}
