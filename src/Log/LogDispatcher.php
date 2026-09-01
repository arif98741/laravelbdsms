<?php

namespace Xenon\LaravelBDSms\Log;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log as LaravelLog;
use Throwable;
use Xenon\LaravelBDSms\Facades\Logger;

/**
 * Single place that decides where an sms log goes. Both the direct send and the
 * queued job route through here, so a driver only has to be taught once.
 */
class LogDispatcher
{
    /**
     * @param array $logData values json encoded, as the database driver expects
     * @param array|null $fileData richer variant for the file driver, when the caller has one
     * @return void
     */
    public static function dispatch(array $logData, ?array $fileData = null): void
    {
        $config = Config::get('sms');

        if (empty($config['sms_log'])) {
            return;
        }

        foreach (self::drivers($config) as $driver) {
            //the sms has already gone out by now, so a failing log driver must not
            //fail the send, nor stop the drivers listed after it
            try {
                if ($driver === 'database') {
                    Logger::createLog($logData);
                } elseif ($driver === 'file') {
                    LaravelLog::info('laravelbdsms', $fileData ?? $logData);
                } elseif ($driver === 'discord') {
                    (new DiscordLog)->createLog($logData);
                }
                //an unknown driver name is ignored, as it always has been
            } catch (Throwable $e) {
                self::reportFailure($driver, $e);
            }
        }
    }

    /**
     * @param string $driver
     * @param Throwable $e
     * @return void
     */
    private static function reportFailure($driver, Throwable $e): void
    {
        try {
            LaravelLog::warning('laravelbdsms: the ' . $driver . ' log driver could not store the sms log', [
                'reason' => $e->getMessage(),
            ]);
        } catch (Throwable $ignored) {
            //the file driver itself is broken, there is nothing left to report with
        }
    }

    /**
     * log_driver takes either one driver name or a list of them, so a log can go
     * to several places at once.
     *
     * @param array $config
     * @return array
     */
    private static function drivers(array $config): array
    {
        //an absent log_driver key has always meant database
        $driver = array_key_exists('log_driver', $config) ? $config['log_driver'] : 'database';

        return array_unique(is_array($driver) ? $driver : [$driver], SORT_REGULAR);
    }
}
