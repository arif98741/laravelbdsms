<?php namespace Xenon\LaravelBDSms\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * @method static Xenon\LaravelBDSms\Request get($requestUrl, array $query, bool $verify = false, $timeout = 10.0)
 * @method static Xenon\LaravelBDSms\Request post($requestUrl, array $query, bool $verify = false, $timeout = 10.0)
 * @method static Xenon\LaravelBDSms\Request setQueue(bool $queue)
 * @see \Xenon\LaravelBDSms\Request
 */
class Request extends Facade
{
    /**
     * @return string
     * @version v1.0.36
     * @since v1.0.36
     */
    protected static function getFacadeAccessor(): string
    {
        return 'LaravelBDSmsRequest';
    }
}
