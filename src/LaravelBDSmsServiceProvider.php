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

use Illuminate\Support\Facades\Schema;
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

        $this->app->bind('LaravelBDSmsRequest', function () {
            return new Request;
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @version v1.0.32
     * @since v1.0.31
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/Config/sms.php' => config_path('sms.php'),
        ], 'config');

        $fileNamePattern    = '_create_laravelbd_sms_table.php';
        $migrationFilename  = date('Y_m_d_His') . $fileNamePattern;

        if (!$this->laravelBDSmsMigrationFileExist($fileNamePattern)) {
            $this->publishes([
                __DIR__ . '/Database/migrations/create_laravelbd_sms_table.php.stub' => database_path('migrations/' . $migrationFilename),
            ], 'migrations');
        }
    }

    /**
     * Check if a migration file with the same pattern exists inside database/migrations/*
     *
     * @param string $filename
     * @return bool
     */
    private function laravelBDSmsMigrationFileExist(string $filename): bool
    {
        $existingMigrations = glob(database_path('migrations/') . '*_create_laravelbd_sms_table.php');

        foreach ($existingMigrations as $migration) {
            if (str_contains($migration, $filename)) {
                return true;
            }
        }
        return false;
    }

}
