<?php

namespace App\Controller;

use App\Service\MangoPayService;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use MangoPay;

#[Route('/paiement', name: 'paiement')]
class PaiementController extends AbstractController
{
    private MangoPay\MangoPayApi $mangoPayApi;

    //Constructeur qui sert à l'initialisation de l'api.
    // Les "$_ENV" sont les éléments à compléter dans le .env.local.
    public function __construct()
    {
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $this->mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $this->mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $this->mangoPayApi->Config->TemporaryFolder = $_ENV['TMP_PATH'];

    }

    #[NoReturn] #[Route('/', name: '_updateRegistrationCard')]
    public function updateRegistrationCard(
        MangoPayService $service
    ): Void
    {
        session_start();
        // update register card with registration data from Payline service
        $cardRegister = $this->mangoPayApi->CardRegistrations->Get($_SESSION['cardRegisterId']);
//        $cardRegister->RegistrationData = isset($_GET['data']) ? 'data=' . $_GET['data'] : 'errorCode=' . $_GET['errorCode'];
//        $updatedCard = $service->updateCardRegistration($cardRegister);
    }
}
