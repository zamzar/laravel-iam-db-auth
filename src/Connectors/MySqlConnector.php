<?php

namespace Zamzar\Laravel\Database\Iam\Connectors;

use Exception;
use Illuminate\Database\Connectors\MySqlConnector as Connector;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PDO;
use Throwable;
use Zamzar\Laravel\Database\Iam\Auth\RdsTokenProvider;

class MySqlConnector extends Connector
{
    /**
     * Create a new PDO connection.
     *
     * @param  string  $dsn
     * @param  array  $config
     * @param  array  $options
     * @return \PDO
     *
     * @throws \Exception
     */
    public function createConnection($dsn, array $config, array $options)
    {
        if (!(Arr::get($config, 'iam.use_iam_auth'))) {
            return parent::createConnection($dsn, $config, $options);
        }

        $tokenProvider = new RdsTokenProvider($config);
        $username = Arr::get($config, 'username');
        $password = $tokenProvider->getToken();

        try {
            return $this->createPdoConnection(
                $dsn, $username, $password, $options
            );
        } catch (Exception $e) {
            $password = $tokenProvider->getToken(true);

            return $this->tryAgainIfCausedByLostConnectionOrBadToken(
                $e, $dsn, $username, $password, $options
            );
        }
    }

    /**
     * Handle an exception that occurred during connect execution.
     *
     * @param  \Throwable  $e
     * @param  string  $dsn
     * @param  string  $username
     * @param  string  $password
     * @param  array  $options
     * @return \PDO
     *
     * @throws \Throwable
     */
    protected function tryAgainIfCausedByLostConnectionOrBadToken(
        Throwable $e,
        $dsn,
        $username,
        $password,
        $options
    ) {
        if ($this->causedByLostConnection($e) || $this->causedByBadToken($e)) {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        }

        throw $e;
    }

    /**
     * Determine if the given exception was caused by a bad auth token
     *
     * @param  Exception  $e
     * @return bool
     */
    protected function causedByBadToken(Exception $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, 'Access denied');
    }
}
