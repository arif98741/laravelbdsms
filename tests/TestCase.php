<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;


abstract class TestCase extends BaseTestCase
{
    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Configure any custom package setup here.
        $app['config']->set('sms', [
            'sms_log' => true,
            'log_driver' => 'file',
            'providers' => [],
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
          //  \Xenon\LaravelBDSms\LaravelBDSmsServiceProvider::class,
        ];
    }
}
