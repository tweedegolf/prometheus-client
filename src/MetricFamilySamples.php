<?php

namespace TweedeGolf\PrometheusClient;

class MetricFamilySamples
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $help;

    /**
     * @var Sample[]
     */
    private $samples;

    /**
     * @param string $name
     * @param string $type
     * @param string|null $help
     * @param Sample[] $samples
     */
    public function __construct($name, $type, $help, array $samples = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->help = $help;
        $this->samples = [];
        $this->addSamples($samples);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @return bool
     */
    public function hasHelp()
    {
        return $this->help !== null;
    }

    /**
     * @return Sample[]
     */
    public function getSamples()
    {
        return $this->samples;
    }

    /**
     * @param Sample $sample
     * @return $this
     */
    public function addSample(Sample $sample)
    {
        $this->samples[] = $sample;

        return $this;
    }

    /**
     * @param Sample[] $samples
     * @return $this
     */
    public function addSamples(array $samples)
    {
        foreach ($samples as $sample) {
            $this->samples[] = $sample;
        }

        return $this;
    }
}
