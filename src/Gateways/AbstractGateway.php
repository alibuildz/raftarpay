<?php

namespace RaftarPay\Gateways;

use GuzzleHttp\Client;
use RaftarPay\Contracts\Gateway;
use RaftarPay\Exceptions\RaftarPayException;

abstract class AbstractGateway implements Gateway
{
    protected array $config;
    protected string $environment;
    protected ?Client $http = null;

    public function __construct(array $config, string $environment = 'sandbox')
    {
        $this->config = $config;
        $this->environment = $environment;
    }

    public function name(): string
    {
        return static::GATEWAY_NAME;
    }

    protected function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    protected function config(string $key, bool $required = true): mixed
    {
        $value = $this->config[$key] ?? null;

        if ($required && ($value === null || $value === '')) {
            throw RaftarPayException::missingConfig($this->name(), $key);
        }

        return $value;
    }

    protected function http(): Client
    {
        if ($this->http === null) {
            $this->http = new Client([
                'timeout'         => 30,
                'connect_timeout' => 15,
                'http_errors'     => false,
            ]);
        }

        return $this->http;
    }

    /**
     * Inject a Guzzle client (used by the test-suite to stub HTTP).
     */
    public function setHttpClient(Client $client): static
    {
        $this->http = $client;

        return $this;
    }

    /**
     * Constant-time signature comparison to avoid timing attacks.
     */
    protected function hashEquals(string $expected, string $actual): bool
    {
        return hash_equals(strtolower($expected), strtolower($actual));
    }
}
