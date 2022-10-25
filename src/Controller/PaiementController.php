<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use App\Service\MondialRelayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use MangoPay;

#[Route('/annonce/{id}', name: 'paiement')]
class PaiementController extends AbstractController
{
// Controller de callback.
    #[Route('/paiement', name: '_updateRegistrationCard')]
    public function updateRegistrationCard(
        MangoPayService     $service,
        MondialRelayService $mondialRelayService,
        UserRepository      $userRepository,
        AnnonceRepository   $annonceRepository,
                            $id
    ): Response
    {
        //Récupération de l'utilisateur connecté par son email
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        //Récupération de l'annonce achetée par son ID
        $annonce = $annonceRepository->findOneBy(['id' => $id]);
        //Récupération du prix d'origine de l'annonce
        $prixAnnonce = $annonce->getPriceOrigin();
        //Calcul des frais
        $fees = $annonce->getPriceTotal() - $annonce->getPriceOrigin();
        //Récupération du poids de l'article vendu
        $annoncePoids = $annonce->getPoids();
        //Si l'acheteur a choisi la livraison alors le prix et les frais varie selon le poids
        if ($annonce->isBuyerDelivery()) {
            if ($annoncePoids == "0g - 500g") {
                $prixPoids = 5.50;
            } elseif ($annoncePoids == "501g - 1kg") {
                $prixPoids = 6;
            } elseif ($annoncePoids == "1.001kg - 2kg") {
                $prixPoids = 7.50;
            } elseif ($annoncePoids == "2.001kg - 3kg") {
                $prixPoids = 8;
            }
            //Evolution des frais selon le prix au poids pour la livraison
            $fees = $fees + $prixPoids;
        }
        $prixAnnonce = $prixAnnonce + $fees;
        //Instance de l'api avec la même config que le service
        $mangoPayApi = new MangoPay\MangoPayApi();
        $mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
        $mangoPayApi->Config->TemporaryFolder = $_ENV['TMP_PATH'];
        //Récupération de la carte créée lors du paiement placée en session
        $cardRegister = $mangoPayApi->CardRegistrations->Get($_SESSION['idCard']);
        //Recuperation du param "data=" de l'URL de retour si elle est SET sinon erreur.
        $cardRegister->RegistrationData = isset($_GET['data']) ? 'data=' . $_GET['data'] : 'errorCode=' . $_GET['errorCode'];
        //Méthode du service permettant de mettre à jour la carte avec la data récupérée
        // afin de finaliser l'enregistrement de la carte
        $card = $service->updateCardRegistration($cardRegister);
        //On récupère l'id de la carte crée
        $cardId = $card->CardId;
        //Méthode du service permettant de créer un payIn
        $service->createPayin($userConnect, $cardId, $prixAnnonce, $fees, $id);

        //Puis on redirige vers l'endroit où l'on veut.
        return $this->redirectToRoute('paiement_redirection', compact('id'));
    }

    //controller de redirection
    #[Route('/redirection', name: '_redirection')]
    public function redirection(
        MangoPayService        $service,
        UserRepository         $userRepository,
        AnnonceRepository      $annonceRepository,
        MondialRelayService    $mondialRelayService,
        MailerInterface        $mailer,
        EntityManagerInterface $em,
                               $id
    ): Response
    {
        //Récupération de l'utilisateur connecté par son email
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        //Récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($id);
        //Récupération du prix d'origine de l'annonce
        $prixAnnonce = $annonce->getPriceOrigin();
        //Récupération de l'ID du wallet du vendeur
        $sellerWalletId = $annonce->getUser()->getidWallet();
        //Récupération de l'ID mangopay du vendeur
        $sellerId = $annonce->getUser()->getIdMangopay();

        if ($annonce->isBuyerDelivery()) {
            //Envoie de la feuille de livraison par mail au vendeur au format PDF
            $etiquetteLivraison = $mondialRelayService->createEtiquette($userConnect, $annonce, $_SESSION['idRelais']);
            //Création du mail contenant le PDF au vendeur.
            $email = (new TemplatedEmail())
                ->from('plantetflower@gmail.com')
                ->to($annonce->getUser()->getEmail())
                ->subject('Votre etiquette de livraison.')
                // path of the Twig template to render
                ->htmlTemplate('annonce/etiquetteEmail.html.twig')
                // pass variables (name => value) to the template
                ->context([
                    'expiration_date' => new \DateTime('+10 days'),
                    'username' => $annonce->getUser()->getFirstName(),
                    'message' => $etiquetteLivraison
                ]);
            $mailer->send($email);
        } else {
            //Si c'est une transaction main à la main, 2 mails sont envoyés. 1 au vendeur 1 a l'acheteur.
            //afin de confirmer la reception et finalisation le paiement.
            $emailAcheteur = (new TemplatedEmail())
                ->from('plantetflower@gmail.com')
                ->to($userConnect->getEmail())
                ->subject('Confirmation de l\'achat.')
                // path of the Twig template to render
                ->htmlTemplate('annonce/handDeliveryAcheteur.html.twig')
                // pass variables (name => value) to the template
                ->context([
                    'mailVendeur' => $annonce->getUser()->getEmail(),
                    'nomVendeur' => $annonce->getUser()->getLastName(),
                    'prenomVendeur' => $annonce->getUser()->getFirstName(),
                    'phoneVendeur' => $annonce->getUser()->getPhoneNumber(),
                    'url' => 'http://127.0.0.1:8000/annonce/' . $id
                ]);
            $emailVendeur = (new TemplatedEmail())
                ->from('plantetflower@gmail.com')
                ->to($annonce->getUser()->getEmail())
                ->subject('Confirmation de l\'achat.')
                // path of the Twig template to render
                ->htmlTemplate('annonce/handDeliveryVendeur.html.twig')
                // pass variables (name => value) to the template
                ->context([
                    'mailAcheteur' => $userConnect->getEmail(),
                    'nomAcheteur' => $userConnect->getLastName(),
                    'prenomAcheteur' => $userConnect->getFirstName(),
                    'phoneAcheteur' => $userConnect->getPhoneNumber(),
                    'url' => 'http://127.0.0.1:8000/annonce/' . $id
                ]);
            $annonce->setSold(true);
            $annonce->setStatus("sold");
            $userConnect->setMyPurchases(array($annonce->getId()));
            $annonce->setAcheteur($userConnect->getEmail());
            $em->persist($annonce);
            $em->persist($userConnect);
            $em->flush($annonce);
            $em->flush($userConnect);
            $mailer->send($emailAcheteur);
            $mailer->send($emailVendeur);
        }
        //Méthode du service pour exécuter le transfert
        $service->createTransfer($userConnect, $prixAnnonce, $sellerWalletId);
        //On récupère l'ID du compte bancaire du vendeur pour l'injecter en paramètre de la méthode de payOut
        $bankAccount = $service->getBankAccountId($sellerId);
        //Méthode du service pour exécuter le PayOut
        $service->createPayOut($sellerWalletId, $bankAccount, $sellerId, $prixAnnonce);

        //Puis on redirige vers l'endroit où l'on veut.
        return $this->render('annonce/redirectionPaiement.html.twig');
    }
}