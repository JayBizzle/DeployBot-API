<?php

namespace Jaybizzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

class DeployBot
{
    public $api_key;
    public $api_endpoint = 'https://<account>.deploybot.com/api/v1/';
    public $client;
    public $query = [];

    public function __construct($api_key, $account, $client = null)
    {
        $this->api_key = $api_key;
        $this->api_endpoint = $this->parseApiEndpoint($account);

        $this->client = ($client) ?: new Client([
            'base_uri' => $this->api_endpoint,
            'headers'  => [
                'X-Api-Token' => $this->api_key,
                'Accept'      => 'application/json',
            ],
            'debug' => false,
        ]);
    }

    public function parseApiEndpoint($account)
    {
        return str_replace('<account>', $account, $this->api_endpoint);
    }

    /**
     * Dynamically add query parameters or call API endpoints.
     *
     * @param string $name
     * @param array  $args
     *
     * @return object
     */
    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'get') {
            $name = strtolower(substr($name, 3));

            return $this->buildRequest($name, $args);
        } else {
            return $this->addQuery($name, $args);
        }
    }

    /**
     * Trigger a deployment.
     *
     * @return object
     */
    public function triggerDeployment()
    {
        return $this->buildRequest('deployments', [], 'post');
    }

    /**
     * Add query parameters.
     *
     * @param string $name
     * @param array  $args
     *
     * @return $this
     */
    public function addQuery($name, $args)
    {
        $name = $this->snakeCase($name);

        $this->query[$name] = $args[0];

        return $this;
    }

    /**
     * Prepare the request.
     *
     * @param string $resource
     * @param array  $args
     * @param string $method
     *
     * @return object
     */
    public function buildRequest($resource, $args = [], $method = 'get')
    {
        $query = [];

        if (isset($args[0]) && count($args[0]) == 1 && is_int($args[0])) {
            $resource = $resource.'/'.$args[0];
        }

        if (!empty($this->query)) {
            $query = $this->query;
        }

        return $this->sendRequest($resource, $query, $method);
    }

    /**
     * Send the request.
     *
     * @param string $resource
     * @param array  $query
     * @param string $method
     *
     * @return object
     */
    public function sendRequest($resource, $query = [], $method = 'get')
    {
        $option_name = ($method == 'get') ? 'query' : 'json';

        try {
            /** @var ResponseInterface $response */
            $response = $this->client->$method($resource, [$option_name => $query]);
        } catch (ClientException $e) {
            return $e->getResponse()->getBody()->getContents();
        }

        // Reset query parameters
        $this->query = [];

        return json_decode($response->getBody());
    }

    /**
     * Convert camelCase methods to snake_case params.
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public function snakeCase($value, $delimiter = '_')
    {
        if (!ctype_lower($value)) {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
            $value = preg_replace('/\s+/', '', $value);
        }

        return $value;
    }
}
