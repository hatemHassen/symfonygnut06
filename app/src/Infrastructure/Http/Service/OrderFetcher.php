<?php

namespace App\Infrastructure\Http\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

class OrderFetcher
{

    public function __construct(
        protected HttpClientInterface $client,
        protected CacheInterface      $cache,
        protected string              $helloAssoApiClientId,
        protected string              $helloAssoApiClientSecret,
        protected string              $slugAsso
    )
    {
    }

    private function getToken(): string
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

    /**
     * @param array{
     *     userSearchKey: string,
     *     pageSize: string
     *     } $query
     * @return  array< array{
     *            order: array{
     *                     id:string,
     *                     date:string,
     *                     formSlug:string,
     *                     formType:string,
     *                     organizationName:string,
     *                     organizationSlug:string,
     *                         organizationType:string,
     *                     organizationIsUnderColucheLaw:bool,
     *                     formName:string,
     *                     isAnonymous:bool,
     *                     isAmountHidden:bool,
     *                     meta:array{
     *                         createdAt: string,
     *                         updatedAt: string,
     *                     }
     *         },
     *        payer: array{
     *            firstName: string,
     *            lastName: string,
     *            email: string,
     *            country: string,
     *        },
     *        name: string,
     *        user: array{
     *            firstName: string,
     *            lastName: string,
     *        },
     *        priceCategory: string,
     *        ticketUrl: string,
     *        qrCode: string,
     *        tierDescription: string,
     *        tierId: string,
     *        id: string,
     *        amount: float,
     *        type: string,
     *        initialAmount: float,
     *        state: string,
     *        }>
     * /
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function fetchAllOrders(array $query): array
    {
        $cacheKey = sprintf('orders_%d', $query['pageSize']);

        return $this->cache->get($cacheKey, function ($item) use ($query) {
            $query = array_merge($query, [
                    'withDetails' => 'false',
                    'sortOrder' => 'Desc',
                    'sortField' => 'Date',
                    'itemStates' => 'Processed',
                    'withCount' => 'true',
                    'pageIndex' => 1,
            ]);

            $params = http_build_query($query);
            $apiUrl = sprintf("https://api.helloasso.com/v5/organizations/%s/items?%s", $this->slugAsso, $params);
            $allOrders = [];
            $token = null;

            $headers = [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept' => 'application/json',
            ];

            while (true) {

                try {
                    $params = [];
                    if ($token !== null) {
                        $params['continuationToken'] = $token;
                    }

                    $response = $this->client->request('GET', $apiUrl, [
                        'headers' => $headers,
                        'query' => $params,
                    ]);

                    $data = $response->toArray(false);
                    $orders = $data['data'] ?? [];
                    $allOrders = array_merge($allOrders, $orders);

                    $token = $data['pagination']['continuationToken'] ?? null;

                    if ($data['data'] === [] || $token === null) {
                        break; // No more pages, exit loop
                    }
                } catch (\Exception $e) {
                    // Handle exception if needed
                    dump('Error occurred: ' . $e->getMessage());
                    break; // Exit the loop on error
                }
            }

            $item->expiresAfter(3600);
            return $allOrders;
        });
    }
}
