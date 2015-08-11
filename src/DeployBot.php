<?php

namespace Jaybizzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class DeployBot
{
    private $api_key;
    private $api_endpoint = 'https://<account>.deploybot.com/api/v1/';
    private $client;
    private $query = [];

    public function __construct($api_key, $account)
    {
        $this->api_key = $api_key;
        $this->api_endpoint = str_replace('<account>', $account, $this->api_endpoint);

        $this->client = new Client([
            'base_url' => $this->api_endpoint,
            'defaults' => [
                'headers'  => [
                    'X-Api-Token' => $this->api_key,
                    'Accept'      => 'application/json',
                ],
            ],
            'debug' => false,
        ]);
    }

    /**
     * Dynamically add query parameters or call API endpoints.
     * 
     * @param string $method
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
     * @param string $method
     * @param array  $args
     *
     * @return $this
     */
    private function addQuery($name, $args)
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
    private function buildRequest($resource, $args = [], $method = 'get')
    {
        $query = [];

        if (count($args[0]) == 1 && is_int($args[0])) {
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
     * @param array  $args
     * @param string $method
     *
     * @return object
     */
    private function sendRequest($resource, $query = [], $method = 'get')
    {
        $option_name = ($method == 'get') ? 'query' : 'json';

        try {
            $response = $this->client->$method($resource, [$option_name => $query]);
        } catch (ClientException $e) {
            return $e->getResponse()->getBody()->getContents();
        }

        // Reset query parameters
        $this->query = [];

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Convert camelCase methods to snake_case params.
     * 
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    private function snakeCase($value, $delimiter = '_')
    {
        $key = $value.$delimiter;

        if (!ctype_lower($value)) {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
            $value = preg_replace('/\s+/', '', $value);
        }

        return $value;
    }
}
