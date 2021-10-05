<?php namespace Xenon\LaravelBDSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void shoot(string $mobile, string $text)
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
