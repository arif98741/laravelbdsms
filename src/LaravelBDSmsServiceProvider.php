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
use Xenon\LaravelBDSms\Log\Log;

class LaravelBDSmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     * @version v1.0.32
     * @since v1.0.31
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

        $this->app->bind('LaravelBDSmsLogger', function () {
            return new Log;
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @version v1.0.32
     * @since v1.0.31
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/sms.php' => config_path('sms.php'),
        ]);


        if ($this->app->runningInConsole())

            if (!class_exists('CreateLaravelbdSmsTable')) {

                $this->publishes([
                    __DIR__ . '/Database/migrations/create_laravelbd_sms_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_laravelbd_sms_table.php'),

                ], 'migrations');
            }
    }

}
