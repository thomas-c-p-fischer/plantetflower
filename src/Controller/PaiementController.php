<?php

namespace App\Controller;

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
        Session         $session,
    ): Response
    {
        session_start();
        //instance de l'api avec la meme config que le service
        $mangoPayApi = new MangoPay\MangoPayApi();
        $mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $mangoPayApi->Config->TemporaryFolder = $_ENV['TMP_PATH'];
        // Recuperation de la carte créée lors du paiement placée en session
        $cardRegister = $mangoPayApi->CardRegistrations->Get($_SESSION['idCard']);
        //Recuperation du param "data=" de l'url de retour si ell est set sinon erreur.
        $cardRegister->RegistrationData = isset($_GET['data']) ? 'data=' . $_GET['data'] : 'errorCode=' . $_GET['errorCode'];
        //Methode du service du service permettant de update la carte avec la carte avec la data recuperer afin de finaliser l'enregistrement de la carte
        $service->updateCardRegistration($cardRegister);

        //Puis on redirige vers l'endroit ou l'ont veut.
        return $this->redirectToRoute('annonce_ajouter');
    }

}
