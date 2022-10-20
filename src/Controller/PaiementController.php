<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use MangoPay;
use MangoPay\Libraries\ResponseException;

#[Route('/annonce/{id}', name: 'paiement')]
class PaiementController extends AbstractController
{
// Controller de callback.
    #[Route('/paiement', name: '_updateRegistrationCard')]
    public function updateRegistrationCard(
        MangoPayService   $service,
        UserRepository    $userRepository,
        AnnonceRepository $annonceRepository,
        Request           $request,
                          $id
    ): Response
    {
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        $annonce = $annonceRepository->findOneBy(['id' => $id]);
        $prixAnnonce = $annonce->getPriceTotal();
        $fees = $annonce->getPriceTotal() - $annonce->getPriceOrigin();
        $annoncePoids = $annonce->getPoids();

        if ($annonce->isBuyerDelivery()) {
            if ($annoncePoids == "0g - 500g") {
                $prixPoids = 5;
            } elseif ($annoncePoids == "501g - 1kg") {
                $prixPoids = 5.5;
            } elseif ($annoncePoids == "1.1kg - 2kg") {
                $prixPoids = 7.5;
            } elseif ($annoncePoids == "2.1kg - 3kg") {
                $prixPoids = 7.5;
            }
            $prixAnnonce = $prixAnnonce + $prixPoids;
            
        }
        //Instance de l'api avec la même config que le service
        $mangoPayApi = new MangoPay\MangoPayApi();
        $mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $mangoPayApi->Config->TemporaryFolder = $_ENV['TMP_PATH'];
        //Récuperation de la carte créée lors du paiement placée en session
        $cardRegister = $mangoPayApi->CardRegistrations->Get($_SESSION['idCard']);
        //Recuperation du param "data=" de l'url de retour si ell est set sinon erreur.
        $cardRegister->RegistrationData = isset($_GET['data']) ? 'data=' . $_GET['data'] : 'errorCode=' . $_GET['errorCode'];
        //Méthode du service permettant de update la carte avec la data récupérer afin de finaliser l'enregistrement de la carte
        $card = $service->updateCardRegistration($cardRegister);
        $cardId = $card->CardId;
        $payIn = $service->createPayin($userConnect, $cardId, $prixAnnonce, $fees);

        //Puis on redirige vers l'endroit où l'on veut.
        return $this->redirectToRoute('annonce_ajouter');
    }

}