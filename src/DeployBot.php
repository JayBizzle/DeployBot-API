<?php

namespace Jaybizzle;

use GuzzleHttp\Client;

class DeployBot
{
    private $api_key;
    private $api_endpoint = 'https://<account>.deploybot.com/api/v1/';
    private $client;

    public function __construct($api_key, $account)
    {
        $this->api_key = $api_key;
        $this->api_endpoint = str_replace('<account>', $account, $this->api_endpoint);

        $this->client = new Client([
            'base_uri' => $this->api_endpoint,
            'headers' => [
                'X-Api-Token' => $this->api_key,
                'Accept' => 'application/json'
            ],
            'debug' => false
        ]);
    }

    public function __call($method, $args)
    {
        return $this->makeRequest($method, $args);
    }

    private function makeRequest($method, $args = array())
    {
        $query = [];
        if(count($args[0]) == 1 && is_int($args[0])) {
            $method = $method.'/'.$args[0];
        }

        if(isset($args[1]['query'])) {
            $query = $args[1]['query'];
        }

         if(isset($args[0]['query'])) {
            $query = $args[0]['query'];
        }

        $response = $this->client->get($method, ['query' => $query]);

        return json_decode($response->getBody()->getContents());
    }
}