<?php namespace Xenon\LaravelBDSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void shoot(string $mobile, string $text)
 *
 * @see \Xenon\LaravelBDSms\SMS
 */
class SMS extends Facade
{
    /**
     * @return string
     * @version v1.0.32
     * @since v1.0.31
     */
    protected static function getFacadeAccessor(): string
    {
        return 'LaravelBDSms';
    }
}
