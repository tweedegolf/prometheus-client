<?php

namespace TweedeGolf\PrometheusClient\Collector;

use TweedeGolf\PrometheusClient\MetricFamilySamples;

interface CollectorInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string|null
     */
    public function getHelp();

    /**
     * @return MetricFamilySamples[]
     */
    public function collect();
}

