<?php

// Service HelloAssoApiService
namespace App\Service;



use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HelloAssoApiService
{

    public function __construct(
        private readonly HelloAssoAuthService $helloAssoAuthService,
        private HttpClientInterface $client,
    )
    {

    }

    public function makeApiCall(string $url, array $headers = [], string $method = 'GET')
    {
        $bearerToken = $this->helloAssoAuthService->getToken();
        $options['auth_bearer'] =$bearerToken;;
        $options['headers']['accept'] = 'application/json';
        try {
            $response = $this->client->request($method, $url, $options);
            return json_decode($response->getContent(false), true);
        }  catch (TransportExceptionInterface $e) {
            return ['data' => [], 'pagination' => [], 'error' => $e->getMessage()];
        }
    }


}
