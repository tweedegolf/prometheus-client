<?php

namespace TweedeGolf\PrometheusClient\Format;

use TweedeGolf\PrometheusClient\MetricFamilySamples;

class TextFormatter implements FormatterInterface
{
    /**
     * @inheritDoc
     */
    public function getMimeType()
    {
        return 'text/plain; version=0.0.4';
    }

    /**
     * @inheritDoc
     */
    public function format(array $metricFamilySamples)
    {
        $lines = [];

        /** @var MetricFamilySamples $sampleFamily */
        foreach ($metricFamilySamples as $sampleFamily) {
            if ($sampleFamily->hasHelp()) {
                $escapedHelp = str_replace(['\\', "\n"], ['\\\\', '\\n'], $sampleFamily->getHelp());
                $lines[] = "# HELP {$sampleFamily->getName()} {$escapedHelp}";
            }
            $lines[] = "# TYPE {$sampleFamily->getName()} {$sampleFamily->getType()}";

            foreach ($sampleFamily->getSamples() as $sample) {
                $labels = [];
                foreach ($sample->getLabels() as $labelName => $labelValue) {
                    $escapedLabelValue = str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\\\n'], (string)$labelValue);
                    $labels[] = "{$labelName}=\"{$escapedLabelValue}\"";
                }

                $metric = $sample->getName();
                if (count($labels) > 0) {
                    $metric .= "{".implode(',', $labels)."}";
                }

                $lines[] = "{$metric} {$sample->getValue()}";
            }

            $lines[] = "";
        }

        return implode("\n", $lines);
    }
}
