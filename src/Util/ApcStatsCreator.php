<?php

namespace TweedeGolf\PrometheusClient\Util;

use TweedeGolf\PrometheusClient\Collector\CallbackGauge;
use TweedeGolf\PrometheusClient\CollectorRegistry;

class ApcStatsCreator
{
    public static function make(CollectorRegistry $registry)
    {
        if (function_exists('apc_cache_info') && !function_exists('apcu_cache_info')) {
            $apcNumSlots = new CallbackGauge('apc_num_slots');
            $apcNumSlots->addCallback(function () {
                return apc_cache_info('user', true)['num_slots'];
            });
            $registry->register($apcNumSlots);

            $apcTtl = new CallbackGauge('apc_ttl');
            $apcTtl->addCallback(function () {
                return apc_cache_info('user', true)['ttl'];
            });
            $registry->register($apcTtl);

            $apcNumHits = new CallbackGauge('apc_num_hits');
            $apcNumHits->addCallback(function () {
                return apc_cache_info('user', true)['num_hits'];
            });
            $registry->register($apcNumHits);

            $apcNumMisses = new CallbackGauge('apc_num_misses');
            $apcNumMisses->addCallback(function () {
                return apc_cache_info('user', true)['num_misses'];
            });
            $registry->register($apcNumMisses);

            $apcStartTime = new CallbackGauge('apc_start_time');
            $apcStartTime->addCallback(function () {
                return apc_cache_info('user', true)['start_time'];
            });
            $registry->register($apcStartTime);
        }
    }
}
