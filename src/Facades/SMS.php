<?php namespace Xenon\LaravelBDSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Xenon\LaravelBDSms\SMS via(string $provider)
 * @method static mixed shoot(string $mobile, string $text)
 *
 * @see \Xenon\LaravelBDSms\SMS
 */
class SMS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'LaravelBDSms';
    }
}
