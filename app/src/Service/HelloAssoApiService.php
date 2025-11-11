<?php

// Service HelloAssoApiService
namespace App\Service;

use GuzzleHttp\Client;

class HelloAssoApiService
{
    private $client;
    private $helloAssoAuthService;

    public function __construct(HelloAssoAuthService $helloAssoAuthService)
    {
        $this->client = new Client();
        $this->helloAssoAuthService = $helloAssoAuthService;
    }

    public function makeApiCall(string $url, array $headers = [], string $method = 'GET')
    {
        $bearerToken = $this->helloAssoAuthService->getToken();
        $authorization = "Bearer " . $bearerToken;
        $headers['authorization'] = $authorization;
        try {
            $response = $this->client->request($method, $url, [
                'accept' => 'application/json',
                'headers' => $headers,
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            // Gérer l'exception ou la logger
            // return $e;
            return false;
        }
    }

    /**
     * @param string $userEmail
     * @param int $page
     * @param int $limit
     * @return array{
     *     data:array< array{
     *         order: array{
     *                  id:string,
     *                  date:string,
     *                  formSlug:string,
     *                  formType:string,
     *                  organizationName:string,
     *                  organizationSlug:string,
     *                      organizationType:string,
     *                  organizationIsUnderColucheLaw:bool,
     *                  formName:string,
     *                  isAnonymous:bool,
     *                  isAmountHidden:bool,
     *                  meta:array{
     *                      createdAt: string,
     *                      updatedAt: string,
     *                  }
     *      },
     *     payer: array{
     *         firstName: string,
     *         lastName: string,
     *         email: string,
     *         country: string,
     *     },
     *     name: string,
     *     user: array{
     *         firstName: string,
     *         lastName: string,
     *     },
     *     priceCategory: string,
     *     ticketUrl: string,
     *     qrCode: string,
     *     tierDescription: string,
     *     tierId: string,
     *     id: string,
     *     amount: float,
     *     type: string,
     *     initialAmount: float,
     *     state: string,
     *     }>,
     *     pagination:array{
     *         pageSize:int,
     *         pageIndex:int,
     *         totalPages:int,
     *         totalCount:int,
     *         continuationToken: string
     *     }
     * }|false
     */
    public function getOrders(string $userEmail, int $page = 1, int $limit = 10): array|false
    {
        $params = http_build_query([
            'userSearchKey' => $userEmail,
            'pageIndex' => $page,
            'pageSize' => $limit,
            'withDetails' => 'false',
            'sortOrder' => 'Desc',
            'sortField' => 'Date',
            'itemStates' => 'Processed',
            'withCount' => 'true'
        ]);
        $url = "https://api.helloasso.com/v5/organizations/" . $_ENV['SLUGASSO']  . "/items?" . $params;
        return $this->makeApiCall($url);
    }
    // Autres méthodes pour interagir avec l'API HelloAsso...
}
