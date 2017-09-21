<?php

namespace TweedeGolf\PrometheusClient\Util;

use TweedeGolf\PrometheusClient\Collector\CallbackGauge;
use TweedeGolf\PrometheusClient\CollectorRegistry;

class ApcuStatsCreator
{
    public static function make(CollectorRegistry $registry)
    {
        if (function_exists('apcu_cache_info')) {
            $apcuNumSlots = new CallbackGauge('apcu_num_slots');
            $apcuNumSlots->addCallback(function () {
                return apcu_cache_info(true)['num_slots'];
            });
            $registry->register($apcuNumSlots);

            $apcuTtl = new CallbackGauge('apcu_ttl');
            $apcuTtl->addCallback(function () {
                return apcu_cache_info(true)['ttl'];
            });
            $registry->register($apcuTtl);

            $apcuNumHits = new CallbackGauge('apcu_num_hits');
            $apcuNumHits->addCallback(function () {
                return apcu_cache_info(true)['num_hits'];
            });
            $registry->register($apcuNumHits);

            $apcuNumMisses = new CallbackGauge('apcu_num_misses');
            $apcuNumMisses->addCallback(function () {
                return apcu_cache_info(true)['num_misses'];
            });
            $registry->register($apcuNumMisses);

            $apcuStartTime = new CallbackGauge('apcu_start_time');
            $apcuStartTime->addCallback(function () {
                return apcu_cache_info(true)['start_time'];
            });
            $registry->register($apcuStartTime);
        }
    }
}
