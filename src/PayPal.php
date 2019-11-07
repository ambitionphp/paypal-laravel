<?php

namespace PulkitJalan\PayPal;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PayPal
{
    /**
     * @var PayPal\Rest\ApiContext
     */
    protected $apiContext;

    /**
     * @var array
     */
    protected $config;

    /**
     * PayPal constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the paypal api context.
     *
     * @param  array $config
     *
     * @return PayPal\Rest\ApiContext
     */
    public function getApiContext(array $config = [], $id = null, $secret = null)
    {
        if (! $this->apiContext) {
            $this->apiContext = new ApiContext($this->getCredentials($id, $secret));

            $config = array_merge([
                'mode' => Arr::get($this->config, 'mode', 'sandbox'),
                'log.LogEnabled' => Arr::get($this->config, 'log.enabled', false),
                'log.FileName' => storage_path('logs/'.Arr::get($this->config, 'log.file', 'laravel.log')),
                'log.LogLevel' => Arr::get($this->config, 'log.level', 'DEBUG'),
            ], $config);
        }

        $this->apiContext->setConfig(array_merge($this->apiContext->getConfig(), $config));

        return $this->apiContext;
    }

    /**
     * Get paypal credentials
     *
     * @param  string $id
     * @param  string $secret
     *
     * @return PayPal\Auth\OAuthTokenCredential
     */
    public function getCredentials($id = null, $secret = null)
    {
        return new OAuthTokenCredential(
            $id ?: Arr::get($this->config, 'client_id'),
            $secret ?: Arr::get($this->config, 'client_secret')
        );
    }

    /**
     * Magic call method.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $api = 'PayPal\Api\\'.Str::studly($method);

        if (class_exists($api)) {
            $class = new \ReflectionClass($api);

            return $class->newInstanceArgs($parameters);
        }

        throw new \BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
    }
}
