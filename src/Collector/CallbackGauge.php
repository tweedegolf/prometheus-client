<?php

namespace TweedeGolf\PrometheusClient\Collector;

use TweedeGolf\PrometheusClient\CollectorRegistry;
use TweedeGolf\PrometheusClient\MetricFamilySamples;
use TweedeGolf\PrometheusClient\Sample;
use TweedeGolf\PrometheusClient\Validator;

class CallbackGauge implements CollectorInterface
{
    private $name;

    private $help;

    private $labelNames;

    private $samplers;

    public function __construct($name, array $labelNames = [], $help = null)
    {
        $this->name = $name;
        $this->help = $help;
        $this->labelNames = $labelNames;
        $this->samplers = [];
    }

    public function addCallback($callback, array $labelValues = [])
    {
        Validator::validateLabelValues($labelValues, $this->labelNames);

        $this->samplers[] = [
            'labelValues' => $labelValues,
            'callback' => $callback,
        ];
    }

    public function getType()
    {
        return Gauge::TYPE;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getHelp()
    {
        return $this->help;
    }

    public function collect()
    {
        $samples = [];
        foreach ($this->samplers as $sampler) {
            $callback = $sampler['callback'];
            $value = $callback();
            $samples[] = new Sample($this->getName(), $this->labelNames, $sampler['labelValues'], $value);
        }

        return [
            new MetricFamilySamples($this->getName(), $this->getType(), $this->getHelp(), $samples),
        ];
    }
}
