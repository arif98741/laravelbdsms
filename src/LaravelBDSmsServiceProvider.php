<?php
/*
 *  Last Modified: 6/28/21, 11:18 PM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms;

use Illuminate\Support\ServiceProvider;

class LaravelBDSmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind('LaravelBDSms', function () {

            $provider = config('sms.default_provider');

            $sender = Sender::getInstance();
            $sender->setProvider($provider);
            $sender->setConfig(config('sms.providers')[$provider]);
            return new SMS($sender);
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/sms.php' => config_path('sms.php'),
        ]);
    }
}
