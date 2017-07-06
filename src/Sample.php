<?php

namespace TweedeGolf\PrometheusClient;

class Sample
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $labelNames;

    /**
     * @var mixed[]
     */
    private $labelValues;

    /**
     * @var int|float
     */
    private $value;

    /**
     * @param string $name
     * @param string[] $labelNames
     * @param mixed[] $labelValues
     * @param float $value
     */
    public function __construct($name, array $labelNames, array $labelValues, $value)
    {
        $this->name = $name;
        $this->labelNames = $labelNames;
        $this->labelValues = $labelValues;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed[]
     */
    public function getLabels()
    {
        return array_combine($this->labelNames, $this->labelValues);
    }

    /**
     * @return string[]
     */
    public function getLabelNames()
    {
        return $this->labelNames;
    }

    /**
     * @return mixed[]
     */
    public function getLabelValues()
    {
        return $this->labelValues;
    }

    /**
     * @return int|float
     */
    public function getValue()
    {
        return $this->value;
    }
}
