<?php

namespace App\Infrastructure\Http\Controller;

use App\Infrastructure\Http\Service\OrderFetcher;
use App\Infrastructure\Http\Service\PaymentFetcher;
use App\Infrastructure\Http\ValueObject\HelloAsso\Collection\OrderCollection;
use App\Infrastructure\Http\ValueObject\HelloAsso\Collection\PaymentCollection;
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
        protected OrderFetcher   $orderFetcher,
        protected PaymentFetcher $paymentFetcher,
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
    #[Route('/payments', name: 'app_profil_payments')]
    public function payments(): Response
    {

        return $this->render('profil/payments/payments.html.twig');
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

    #[Route('/payments_data', name: 'app_profil_payments_data')]
    public function paymentsData(Request $request): JsonResponse
    {
        $start = $request->query->getInt('start');
        $length = $request->query->getInt('length', 10);

        $pageNumber = intdiv($start, $length)+1;
        $query = [
            'userSearchKey' => $this->getUser()->getUserIdentifier(),
            'pageSize' => $length,
        ];

        $payments = $this->paymentFetcher->fetchAllPayments($query);
        $orderCollection = PaymentCollection::fromHelloAssoResponse($payments);
        return new JsonResponse([
            'recordsTotal' => $orderCollection->getTotalCount() ,
            'recordsFiltered' => $orderCollection->getFilteredCount(),
            'data' => $orderCollection->getOrders($pageNumber, $length),
        ]);
    }

}
