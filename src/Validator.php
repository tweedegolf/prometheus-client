<?php

namespace TweedeGolf\PrometheusClient;

class Validator
{
    const METRIC_REGEX = '/^[a-zA-Z_:][a-zA-Z0-9_:]*$/';

    const LABEL_REGEX = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

    /**
     * @param string $labelName
     * @return bool
     */
    public static function validateLabel($labelName)
    {
        if (!is_string($labelName) || strlen($labelName) === 0) {
            return false;
        }

        if (preg_match(self::LABEL_REGEX, $labelName) !== 1) {
            return false;
        }

        if (strlen($labelName) >= 2 && $labelName[0] === '_' && $labelName[1] === '_') {
            return false;
        }

        return true;
    }

    /**
     * @param string[] $labelNames
     * @param string[] $disallowed
     * @throws PrometheusException
     */
    public static function validateAllLabels(array $labelNames, array $disallowed = [])
    {
        foreach ($labelNames as $labelName) {
            if (!self::validateLabel($labelName)) {
                throw new PrometheusException("Label '{$labelName}' is not a valid label name");
            }

            if (in_array($labelName, $disallowed, true)) {
                throw new PrometheusException("Label '{$labelName}' is not allowed");
            }
        }
    }

    /**
     * @param mixed[] $labelValues
     * @param string[] $labelNames
     * @throws PrometheusException
     * @return bool
     */
    public static function validateLabelValues(array $labelValues, array $labelNames)
    {
        $requiredCount = count($labelNames);
        $actualCount = count($labelValues);
        if ($actualCount !== $requiredCount) {
            throw new PrometheusException("Invalid labels have been given, {$requiredCount} required but {$actualCount} given");
        }

        return true;
    }

    /**
     * @param string[]|string $name
     * @return string
     * @throws PrometheusException
     */
    public static function makeName($name)
    {
        $name = is_array($name) ? implode('_', $name) : $name;

        if (!is_string($name)) {
            throw new PrometheusException("Name for metric is not a string");
        }

        if (strlen($name) === 0) {
            throw new PrometheusException("Name of a metric must be longer than zero");
        }

        if (preg_match(self::METRIC_REGEX, $name) !== 1) {
            throw new PrometheusException("Name '{$name}' is not a valid metric name");
        }

        return $name;
    }
}
