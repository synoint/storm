<?php

namespace Cint\Demand\Factories;

use Syno\Cint\Demand\Client;
use Syno\Cint\HttpClient;

class ClientFactory
{
    /** @var HttpClient */
    private $httpClient;

    /** @var Client */
    private $client;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function __invoke(string $apiDomain): Client
    {
        return $this->client = new Client($this->httpClient, $apiDomain);
    }

    public function loadClientApiKey(string $apiKey): Client
    {
        return $this->client->setApiKey($apiKey);
    }
}