<?php

namespace TweedeGolf\PrometheusClient\Util;

use TweedeGolf\PrometheusClient\Collector\CallbackGauge;
use TweedeGolf\PrometheusClient\CollectorRegistry;

class OpcacheStatsCreator
{
    public static function make(CollectorRegistry $registry)
    {
        if (function_exists('opcache_get_status')) {
            $stats = opcache_get_status(false);

            if ($stats !== false && $stats['opcache_enabled']) {
                $stat = new CallbackGauge('opcache_full');
                $stat->addCallback(function () use ($stats) {
                    return $stats['cache_full'] ? 1 : 0;
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_restart_pending');
                $stat->addCallback(function () use ($stats) {
                    return $stats['restart_pending'] ? 1 : 0;
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_restart_in_progress');
                $stat->addCallback(function () use ($stats) {
                    return $stats['restart_in_progress'] ? 1 : 0;
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_memory_used');
                $stat->addCallback(function () use ($stats) {
                    return $stats['memory_usage']['used_memory'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_memory_free');
                $stat->addCallback(function () use ($stats) {
                    return $stats['memory_usage']['free_memory'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_memory_wasted');
                $stat->addCallback(function () use ($stats) {
                    return $stats['memory_usage']['wasted_memory'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_interned_strings_buffer_size');
                $stat->addCallback(function () use ($stats) {
                    return $stats['interned_strings_usage']['buffer_size'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_interned_strings_used');
                $stat->addCallback(function () use ($stats) {
                    return $stats['interned_strings_usage']['used_memory'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_interned_strings_free');
                $stat->addCallback(function () use ($stats) {
                    return $stats['interned_strings_usage']['free_memory'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_interned_strings_count');
                $stat->addCallback(function () use ($stats) {
                    return $stats['interned_strings_usage']['number_of_strings'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_num_cached_scripts');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['num_cached_scripts'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_num_cached_keys');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['num_cached_keys'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_max_cached_keys');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['max_cached_keys'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_hits');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['hits'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_start_time');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['start_time'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_last_restart_time');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['last_restart_time'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_oom_restarts');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['oom_restarts'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_hash_restarts');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['hash_restarts'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_manual_restarts');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['manual_restarts'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_misses');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['misses'];
                });
                $registry->register($stat);

                $stat = new CallbackGauge('opcache_stats_blacklist_misses');
                $stat->addCallback(function () use ($stats) {
                    return $stats['opcache_statistics']['blacklist_misses'];
                });
                $registry->register($stat);
            }
        }
    }
}
