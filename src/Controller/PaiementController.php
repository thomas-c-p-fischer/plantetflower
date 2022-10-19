<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use MangoPay;
use MangoPay\Libraries\ResponseException;

#[Route('/annonce', name: 'paiement')]
class PaiementController extends AbstractController
{
// Controller de callback.
    #[Route('/paiement', name: '_updateRegistrationCard')]
    public function updateRegistrationCard(
        MangoPayService $service,
        UserRepository  $userRepository,
    ): Response
    {
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);

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
        $payIn = $service->createPayin($userConnect, $cardId);

        //Puis on redirige vers l'endroit où l'on veut.
        return $this->redirectToRoute('annonce_ajouter');
    }

}