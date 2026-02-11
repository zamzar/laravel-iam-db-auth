<?php

namespace Zamzar\Laravel\Database\Iam\Auth;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class RdsTokenProvider
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var AuthTokenGenerator
     */
    protected $authTokenGenerator;

    /**
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->cache = Cache::store(Arr::get($config, 'iam.cache_store'));
        $provider = CredentialProvider::defaultProvider();
        $this->authTokenGenerator = new AuthTokenGenerator($provider);
    }

    /**
     * Retrieve an auth token from the AWS Auth Token Generator
     *
     * @param  bool $refetch Force refetch of cached token
     * @return string
     */
    public function getToken(bool $refetch = false): string
    {
        if ($refetch) {
            $this->cache->forget('db_iam_token');
        }

        // Cache the token for 10 minutes (600 seconds) - max lifetime is 15 minutes
        return $this->cache->remember('db_iam_token', 600, function () {
            return $this->authTokenGenerator->createToken(
                Arr::get($this->config, 'host') . ':' . Arr::get($this->config, 'port'),
                Arr::get($this->config, 'iam.aws_region'),
                Arr::get($this->config, 'username')
            );
        });
    }
}
