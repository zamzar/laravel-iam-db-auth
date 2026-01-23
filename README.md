# laravel-iam-db-auth

A Laravel package for connecting to an AWS RDS instance via [IAM authentication](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/UsingWithRDS.IAMDBAuth.html).

It includes a service provider that gives the framework our overridden MySQL connector class when it asks for a MySQL connection.

## Installation

Require this package with composer:

```shell
composer require zamzar/laravel-iam-db-auth
```

Add an `iam` block to your database config, with the following settings:

```php
    'mysql' => [
        'driver' => 'mysql',
        'url' => env('DB_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'database_name'),
        'username' => env('DB_USERNAME', 'database_username'),
        'password' => env('DB_PASSWORD', ''),
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'iam' => [
            'use_iam_auth' => env('DB_USE_IAM_AUTH', false),
            'aws_region' => env('DB_AWS_REGION', 'us-east-1'),
            'cache_store' => env('DB_IAM_CACHE_STORE', 'file'),
        ],
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
```

## Caching

AWS RDS auth tokens are valid for up to 15 minutes. This package uses Laravel's caching module to cache tokens for 10 minutes, so that it doesn't have to request a new token for every database connection.

The cache store can be defined via the `iam.cache_store` setting in your database configuration.

> [!CAUTION]
> **Do not** use a database cache driver (e.g. `database`) that uses a database connection configured with IAM authentication, as this will cause a recursive loop of the cache driver trying to access the database to retrieve the auth token.
