<?php

namespace Zamzar\Laravel\Database\Iam;

use Illuminate\Support\ServiceProvider;
use Zamzar\Laravel\Database\Iam\Connectors\MySqlConnector;

class DatabaseIamServiceProvider extends ServiceProvider
{
    /**
     * Register the custom database connector.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('db.connector.mysql', MySqlConnector::class);
    }
}
