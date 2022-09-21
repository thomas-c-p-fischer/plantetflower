<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Security;
use nusoap_client;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ApiMondialRelay extends AbstractController
{
    private AnnonceRepository $annonceRepository;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;


    public function __construct(HttpClientInterface $httpClient, AnnonceRepository $annonceRepository, EntityManagerInterface $entityManager)
    {
        $this->httpClient = $httpClient;
        $this->annonceRepository = $annonceRepository;
        $this->entityManager = $entityManager;
    }

    public function createEtiquette(Security $security, $annonce, $destAddress, $livRelId): string
    {
        $user = $security->getUser();
        $expeditor = $annonce->getUser();

        if ($user->getSex() == "homme") {
            $userFullname = "M. " . $user->getLastName() . " " . $user->getFirstName();
        } else {
            $userFullname = "MME " . $user->getLastName() . " " . $user->getFirstName();
        }

        if ($expeditor->getSex() == "homme") {
            $expeditorFullname = "M. " . $expeditor->getLastName() . " " . $expeditor->getFirstName();
        } else {
            $expeditorFullname = "MME " . $expeditor->getLastName() . " " . $expeditor->getFirstName();
        }

        $poids = 0;
        if ($annonce->getPoids() === "0g - 500g") {
            $poids = 499;
        } else if ($annonce->getPoids() === "500g - 1kg") {
            $poids = 999;
        } else if ($annonce->getPoids() === "1kg - 2kg") {
            $poids = 1999;
        } else if ($annonce->getPoids() === "2kg - 3kg") {
            $poids = 2999;
        }

        $MR_WebSiteId = "BDTEST13";
        $MR_WebSiteKey = "PrivateK";
        $client = new nusoap_client("http://api.mondialrelay.com/Web_Services.asmx?WSDL", true);
        $client->soap_defencoding = 'utf-8';

        $params = array(
            'Enseigne' => $MR_WebSiteId,
            'ModeCol' => 'REL',
            'ModeLiv' => '24R',
            'Expe_Langage' => 'FR',
            'Expe_Ad1' => $expeditorFullname,
            'Expe_Ad3' => $annonce->getExpAddress(),
            'Expe_Ville' => $annonce->getVille(),
            'Expe_CP' => $annonce->getExpZipCode(),
            'Expe_Pays' => 'FR',
            'Expe_Tel1' => $expeditor->getPhoneNumber(),
            'Dest_Langage' => 'FR',
            'Dest_Ad1' => $userFullname,
            'Dest_Ad3' => $destAddress,
            'Dest_Ville' => 'ORLY',
            'Dest_CP' => '94310',
            'Dest_Pays' => 'FR',
            'Dest_Tel1' => $user->getPhoneNumber(),
            'Poids' => $poids,
            'NbColis' => 1,
            'CRT_Valeur' => 0,
            'COL_Rel_Pays' => 'XX',
            'COL_Rel' => 'AUTO',
            'LIV_Rel_Pays' => 'FR',
            'LIV_Rel' => $livRelId
        );

        $code = implode("", $params);
        $code .= $MR_WebSiteKey;

        $params["Security"] = strtoupper(md5($code));


        $result = $client->call(
            'WSI2_CreationEtiquette',
            $params,
            'http://api.mondialrelay.com/',
            'http://api.mondialrelay.com/WSI2_CreationEtiquette'
        );

        $pdfEtiquette = 'https://www.mondialrelay.com';
        $pdfEtiquette .= $result ["WSI2_CreationEtiquetteResult"]["URL_Etiquette"];
        $numberExpe = $result ["WSI2_CreationEtiquetteResult"]["ExpeditionNum"];

        // création de du permalink de tracing du colis
        $Tracing_url = "http://www.mondialrelay.fr/public/permanent/tracking.aspx?ens=" . $MR_WebSiteId . "&exp=" . $numberExpe . "&pays=FR&language=FR";
        $Tracing_url .= $this->AddPermalinkSecurityParameters($numberExpe);

        $annonce->setExpNumber($numberExpe);
        $annonce->setEtiquetteURL($pdfEtiquette);
        $annonce->setTracingURL($Tracing_url);
        $this->entityManager->persist($annonce);
        $this->entityManager->flush();

        return $pdfEtiquette;
    }

    private function AddPermalinkSecurityParameters($Chaine): string
    {
        $MR_WebSiteId = "BDTEST13";
        $MR_WebSiteKey = "PrivateK";
        $UrlToSecure = "<" . $MR_WebSiteId . ">" . $Chaine . "<" . $MR_WebSiteKey . ">";
        return "&crc=" . strtoupper(md5($UrlToSecure));
    }


    // requete pour interroger si colis livré à mettre en back toutes les 6h

    public function getStatusTracing(ApiPayOut $ApiPayOut, ApiWallet $ApiWallet, ApiIban $ApiIban): string
    {

        $MR_WebSiteId = "BDTEST13";
        $MR_WebSiteKey = "PrivateK";
        $client = new nusoap_client("http://api.mondialrelay.com/Web_Services.asmx?WSDL", true);
        $client->soap_defencoding = 'utf-8';

        $annonce = $this->annonceRepository->findBy([
            'shipment' => 'exp_number'
        ]);
        foreach ($annonce as $key) {
            $expeditionNumber = $key->getExpNumber();
            if ($expeditionNumber) {
                $params = array(
                    'Enseigne' => $MR_WebSiteId,
                    'Expedition' => intval($expeditionNumber),
                    'Langue' => 'FR',
                );

                $code = implode("", $params);
                $code .= $MR_WebSiteKey;
                $params["Security"] = strtoupper(md5($code));

                $result = $client->call(
                    'WSI2_TracingColisDetaille',
                    $params,
                    'http://api.mondialrelay.com/',
                    'http://api.mondialrelay.com/WSI2_TracingColisDetaille'
                );

                echo '<h2>Result</h2><pre>';
                print_r($result);
                echo '</pre>';

                if ($result ["WSI2_TracingColisDetailleResult"]["STAT"] == '82') {
                    $idAcheteur = intval($key->getAcheteur());
                    $BuyerIdMango = $idAcheteur;
                    $BuyerWallet = $ApiWallet->getWallet($BuyerIdMango);
                    $BuyerWalletId = $BuyerWallet['Id'];
                    $SellerId = $key->getUser()->getIdMangoPay();
                    $SellerWallet = $ApiWallet->getWallet($SellerId);
                    $SellerBankAccount = $ApiIban->GetLastBankAccount($SellerId);
                    $Transfer = $ApiPayOut->Transfer($BuyerIdMango, $SellerId, $BuyerWalletId, $SellerWallet['Id'], $key->getPriceOrigin());

                    if ($Transfer->Status === "ERROR") {
                        return $this->redirectToRoute('errors', [
                            'ResultCode' => $Transfer->ResultCode
                        ]);
                    } else if ($Transfer->Status === "SUCCEEDED") {
                        $PayOut = $ApiPayOut->PayOut($SellerId, $SellerWallet['Id'], $SellerBankAccount['Id'], $key->getPriceOrigin());

                        if ($PayOut->Status === "ERROR") {
                            return $this->redirectToRoute('errors', [
                                'ResultCode' => $PayOut->ResultCode
                            ]);
                        }
                    }
                    $key->setStatus("validated");
                    $key->setToken('');
                    $key->setStatutLivraison(true);

                } else {
                    $key->setStatutLivraison(false);
                }
                $this->entityManager->persist($key);
                $this->entityManager->flush();


                return 'Numéro expédition : ' . $expeditionNumber . ' STAT : ' . $result ["WSI2_TracingColisDetailleResult"]["STAT"] . ' Libellé : ' . $result ["WSI2_TracingColisDetailleResult"]["Libelle01"];
            }
        }
    }
}