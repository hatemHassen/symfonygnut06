<?php

namespace App\Infrastructure\Http\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PaymentFetcher extends HelloAssoFetcher
{

    public function __construct(
        protected HttpClientInterface $client,
        protected CacheInterface      $cache,
        protected string              $helloAssoApiClientId,
        protected string              $helloAssoApiClientSecret,
        protected string              $slugAsso
    )
    {
        parent::__construct(
            $client,
            $cache,
            $helloAssoApiClientId,
            $helloAssoApiClientSecret
        );
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
    public function fetchAllPayments(array $query): array
    {
        $cacheKey = sprintf('paymentsss_%d', $query['pageSize']);

        return $this->cache->get($cacheKey, function ($item) use ($query) {
            $query = array_merge($query, [
                    'withDetails' => 'false',
                    'sortOrder' => 'Desc',
                    'sortField' => 'Date',
                    'states' => 'Authorized',
                    'withCount' => 'true',
                    'pageIndex' => 1,
            ]);

            $params = http_build_query($query);
            $apiUrl = sprintf("https://api.helloasso.com/v5/organizations/%s/payments?%s", $this->slugAsso, $params);

            $allPayments = [];
            $token = null;

            $headers = [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept' => 'application/json',
            ];
            $i=0;
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
                    $allPayments = array_merge($allPayments, $orders);

                    $token = $data['pagination']['continuationToken'] ?? null;
                    if ($data['data'] === [] || $token === null) {
                        break; // No more pages, exit loop
                    }
                    $i++;
                    if($i>10){
                        break;
                    }
                } catch (\Exception $e) {
                    // Handle exception if needed
                    dump('Error occurred: ' . $e->getMessage());
                    break; // Exit the loop on error
                }
            }

            $item->expiresAfter(3600);
            return $allPayments;
        });
    }
}
