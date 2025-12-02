<?php

namespace App\Infrastructure\Http\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class HelloAssoFetcher
{

    public function __construct(
        protected HttpClientInterface $client,
        protected CacheInterface      $cache,
        protected string              $helloAssoApiClientId,
        protected string              $helloAssoApiClientSecret,
    )
    {
    }

    protected function getToken(): string
    {
        return $this->cache->get('helloasso_bearer_token', function ($item) {
            $params = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->helloAssoApiClientId,
                'client_secret' => $this->helloAssoApiClientSecret,
            ];

            $response = $this->client->request('POST', 'https://api.helloasso.com/oauth2/token', [
                'body' => $params,
            ]);

            $dataToken = $response->toArray(false);

            $item->expiresAfter($dataToken['expires_in']);

            return $dataToken['access_token'];
        });
    }
}
