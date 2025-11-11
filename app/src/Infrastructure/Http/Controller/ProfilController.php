<?php

namespace App\Infrastructure\Http\Controller;

use App\Service\HelloAssoApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Service dédié pour les appels API HelloAsso

#[Route('/profil')]
class ProfilController extends AbstractController
{
    private $helloAssoApiService;

    public function __construct(HelloAssoApiService $helloAssoApiService)
    {
        $this->helloAssoApiService = $helloAssoApiService;
    }

    #[Route('', name: 'app_profil')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $userEmail = urlencode($user->getUserIdentifier());
        $page = $request->query->get('page', 1);
        $response = $this->helloAssoApiService->getOrders($userEmail, $page);

        // dump($user);
        $googleMapsApiKey = $_ENV['GNUT06MAPAPI'];
        // exit;
        // Renvoyer à la vue Twig, en passant l'utilisateur comme variable
        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'response' => $response,
            'googleMapsApiKey' => $googleMapsApiKey,
        ]);
    }

    #[Route('/{donnees}/{page}', name: 'app_profil_page', defaults: ['page' => 1])]
    public function page(string $page, string $donnees): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        $userEmail = urlencode($user->getUserIdentifier());
        // Utilisation de la fonction pour construire l'URL
        if ($donnees === 'orders' || $donnees === 'payments') {
            $base = $donnees === 'orders' ? "items" : "payments";
            $url = self::buildHelloAssoUrl($base, $userEmail, $page, $donnees);
        }

        $data_items = $this->helloAssoApiService->makeApiCall($url);
        // dump($user);
        $googleMapsApiKey = $_ENV['GNUT06MAPAPI'];
        // exit;
        // Renvoyer à la vue Twig, en passant l'utilisateur comme variable
        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'data_items' => $data_items,
            'googleMapsApiKey' => $googleMapsApiKey,
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
