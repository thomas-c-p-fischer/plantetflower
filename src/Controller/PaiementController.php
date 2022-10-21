<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MangoPay;

#[Route('/annonce/{id}', name: 'paiement')]
class PaiementController extends AbstractController
{
// Controller de callback.
    #[Route('/paiement', name: '_updateRegistrationCard')]
    public function updateRegistrationCard(
        MangoPayService   $service,
        UserRepository    $userRepository,
        AnnonceRepository $annonceRepository,
                          $id
    ): Response
    {
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        $annonce = $annonceRepository->findOneBy(['id' => $id]);
        $prixAnnonce = $annonce->getPriceOrigin();
        $fees = $annonce->getPriceTotal() - $annonce->getPriceOrigin();
        $annoncePoids = $annonce->getPoids();
        if ($annonce->isBuyerDelivery()) {
            if ($annoncePoids == "0g - 500g") {
                $prixPoids = 4.40;
            } elseif ($annoncePoids == "501g - 1kg") {
                $prixPoids = 4.90;
            } elseif ($annoncePoids == "1.001kg - 2kg") {
                $prixPoids = 6.40;
            } elseif ($annoncePoids == "2.001kg - 3kg") {
                $prixPoids = 6.60;
            }
            $prixAnnonce = $prixAnnonce + $fees + $prixPoids;
            $fees = $fees + $prixPoids;
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
        dump($id);
        $payIn = $service->createPayin($userConnect, $cardId, $prixAnnonce, $fees, $id);

        //Puis on redirige vers l'endroit où l'on veut.
        return $this->redirectToRoute('paiement_redirection', compact('id'));
    }

    //controller de redirection
    #[Route('/redirection', name: '_redirection')]
    public function redirection(
        MangoPayService   $service,
        UserRepository    $userRepository,
        AnnonceRepository $annonceRepository,
                          $id
    ): Response
    {
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        $annonce = $annonceRepository->find($id);
        $prixAnnonce = $annonce->getPriceOrigin();
        $sellerWalletId = $annonce->getUser()->getidWallet();


        $transfer = $service->createTransfer($userConnect, $prixAnnonce, $sellerWalletId);

        //Puis on redirige vers l'endroit où l'on veut.
        return $this->redirectToRoute('annonce_ajouter');
    }
}