<?php

namespace Zamzar\Laravel\Database\Iam;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Zamzar\Laravel\Database\Iam\Connectors\MySqlConnector;
use Zamzar\Laravel\Database\Iam\Connectors\PostgresConnector;

class DatabaseIamServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     * Swap out the default connector and bind our custom one.
     *
     * @return void
     */
    public function register()
    {
        $connections = Config::get('database.connections');
        foreach ($connections as $key => $connection) {
            if (Arr::has($connection, 'use_iam_auth') && Arr::get($connection, 'use_iam_auth')) {
                switch (Arr::get($connection, 'driver')) {
                    case "mysql":
                        $this->app->bind('db.connector.mysql', MySqlConnector::class);
                        break;
                    case "pgsql":
                        $sslMode = Config::get('database.connections.'.$key.'.sslmode', 'verify-full');
                        Config::set('database.connections.'.$key.'.sslmode', $sslMode);

                        $certPath = Config::get(
                            'database.connections.'.$key.'.sslrootcert',
                            realpath(base_path('vendor/zamzar/laravel-iam-db-auth/certs/global-bundle.pem'))
                        );

                        switch (PHP_OS) {
                            case 'WINNT':
                                $certPath = str_replace('\\', '\\\\\\\\', $certPath);
                                break;
                        }
                        Config::set('database.connections.'.$key.'.sslrootcert', "'{$certPath}'");

                        $this->app->bind('db.connector.pgsql', PostgresConnector::class);

                        break;
                }
            }
        }
    }
}
