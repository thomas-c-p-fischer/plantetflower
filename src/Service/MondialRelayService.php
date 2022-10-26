<?php

namespace App\Service;

use App\Entity\Annonce;
use App\Entity\User;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use nusoap_client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MondialRelayService
{
    private $Client;
    private $annonceRepository;
    private $entityManager;

    public function __construct(HttpClientInterface $httpClient, AnnonceRepository $annonceRepository, EntityManagerInterface $entityManager)
    {
        $this->Client = $httpClient;
        $this->annonceRepository = $annonceRepository;
        $this->entityManager = $entityManager;
    }


    public function createEtiquette(User $user, Annonce $annonce, $livRelId)
    {

        $expeditor = $annonce->getUser();

        if ($user->getGender() == "homme") {
            $userFullname = "M. " . $user->getLastName() . " " . $user->getFirstName();
        } else {
            $userFullname = "MME " . $user->getLastName() . " " . $user->getFirstName();
        }

        if ($expeditor->getGender() == "homme") {
            $expeditorFullname = "M. " . $expeditor->getLastName() . " " . $expeditor->getFirstName();
        } else {
            $expeditorFullname = "MME " . $expeditor->getLastName() . " " . $expeditor->getFirstName();
        }

        $poids = 0;
        if ($annonce->getPoids() === "0g - 500g") {
            $poids = 499;
        } else if ($annonce->getPoids() === "501g - 1kg") {
            $poids = 999;
        } else if ($annonce->getPoids() === "1.001kg - 2kg") {
            $poids = 1999;
        } else if ($annonce->getPoids() === "2.001kg - 3kg") {
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
            'Expe_Ad3' => $annonce->getExpAdress(),
            'Expe_Ville' => $annonce->getVille(),
            'Expe_CP' => $annonce->getExpZipCode(),
            'Expe_Pays' => 'FR',
            'Expe_Tel1' => $expeditor->getPhoneNumber(),
            'Dest_Langage' => 'FR',
            'Dest_Ad1' => $userFullname,
            'Dest_Ad3' => $user->getAddress(),
            'Dest_Ville' => $user->getCity(),
            'Dest_CP' => $user->getZipCode(),
            'Dest_Pays' => $user->getCountryOfResidence(),
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

    private function AddPermalinkSecurityParameters($Chaine)
    {
        $MR_WebSiteId = "BDTEST13";
        $MR_WebSiteKey = "PrivateK";
        $UrlToSecure = "<" . $MR_WebSiteId . ">" . $Chaine . "<" . $MR_WebSiteKey . ">";
        return "&crc=" . strtoupper(md5($UrlToSecure));
    }


}