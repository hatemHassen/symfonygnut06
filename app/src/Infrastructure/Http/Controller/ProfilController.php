<?php

namespace App\Infrastructure\Http\Controller;

use App\Infrastructure\Http\Service\OrderFetcher;
use App\Infrastructure\Http\ValueObject\HelloAsso\Collection\OrderCollection;
use App\Service\HelloAssoApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Service dédié pour les appels API HelloAsso

#[Route('/profil')]
class ProfilController extends AbstractController
{


    public function __construct(
        protected OrderFetcher $orderFetcher,
    )
    {
    }

    #[Route('', name: 'app_profil')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig');
    }

    #[Route('/orders', name: 'app_profil_orders')]
    public function orders(): Response
    {

        return $this->render('profil/orders/orders.html.twig');
    }

    #[Route('/orders_data', name: 'app_profil_orders_data')]
    public function ordersData(Request $request): JsonResponse
    {

        $start = $request->query->getInt('start');
        $length = $request->query->getInt('length', 10);

        $pageNumber = intdiv($start, $length)+1;
        $query = [
            'userSearchKey' => $this->getUser()->getUserIdentifier(),
            'pageSize' => $length,
        ];

        $orders = $this->orderFetcher->fetchAllOrders($query);
        $orderCollection = OrderCollection::fromHelloAssoResponse($orders);

        return new JsonResponse([
            'recordsTotal' => $orderCollection->getTotalCount() ,
            'recordsFiltered' => $orderCollection->getFilteredCount(),
            'data' => $orderCollection->getOrders($pageNumber, $length),
        ]);
    }

    #[Route('/orders', name: 'app_profil_payments')]
    public function payments(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        $userEmail = urlencode($user->getUserIdentifier());
        $data_items = $this->helloAssoApiService->makeApiCall(self::buildHelloAssoUrl("items" , $userEmail, 1, "orders" ));
        // exit;
        // Renvoyer à la vue Twig, en passant l'utilisateur comme variable
        return $this->render('profil/orders/orders.html.twig', [
            'data_items' => $data_items
        ]);
    }

    // Fonction pour construire l'URL de base
    private static function buildHelloAssoUrl($base, $userEmail, $page, $type): string
    {
        $slugAsso = $_ENV['SLUGASSO'];
        $pageSize = 10;
        $sortOrder = "Desc";
        $sortField = "Date";
        $url = "https://api.helloasso.com/v5/organizations/$slugAsso/$base?userSearchKey=$userEmail&pageIndex=$page&pageSize=$pageSize&withDetails=false&sortOrder=$sortOrder&sortField=$sortField&withCount=true";

        if ($type === 'orders') {
            $url .= "&itemStates=Processed";
        } elseif ($type === 'payments') {
            $url .= "&states=Authorized";
        }

        return $url;
    }
}
