<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\AnnonceFormType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/annonce', name: 'annonce')]
class AnnonceController extends AbstractController
{
    #[Route('/ajouter/{annonce}', name: '_ajouter')]
    public function createAnnonce(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $annonce = new Annonce();

        $form = $this->createForm(AnnonceFormType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();
            if (count($images) > 3) {
                $form->addError(new FormError("Trop d'images"));
            } elseif (count($images) < 1) {
                $form->addError(new FormError("Vous devez upload au minimum 1 image"));
            }
            if ($form->isValid()) {
                foreach ($images as $image) {
                    $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                    //on copie le fichier dans le dossier uploads
                    $image->move(
                        $this->getParameter('annonces_directory'),
                        $fichier
                    );
                    $img = new Image();
                    $img->setName($fichier);
                    $annonce->addImage($img);
                }

                // récupérer les données du formulaire
                $description = $form->get('description')->getData();
                $shipment = $form->get('shipment')->getData();
                $plantPot = $form->get('plantPot')->getData();
                $preUpperVille = $form->get('ville')->getData();
                $postUpperVille = strtoupper($preUpperVille);

                if ($description) {
                    $annonce->setDescription($description);
                }

                if ($plantPot) {
                    $annonce->setPlantPot(true);
                } else {
                    $annonce->setPlantPot(false);
                }

                if ($shipment) {
                    $annonce->setShipement(true);
                } else {
                    $annonce->setShipement(false);
                }

                $annonce->setPriceOrigin($form->get('priceOrigin')->getData());
                $annonce->setDateExpiration($form->get('dateExpiration')->getData());
                $annonce->setTitle($form->get('title')->getData());
                $annonce->setCategory($form->get('category')->getData());
                $annonce->setPoids($form->get('poids')->getData());
                $annonce->setSold(false);
                $annonce->setStatutLivraison(false);

                if ($form->get('ville')->getData() != null) {
                    $annonce->setVille($postUpperVille);
                };
                $annonce->setUser($this->getUser());
                $annonce->setCreatedAt(new \DateTime());
                $annonce->setExpAdress($form->get('expAddress')->getData());
                $annonce->setExpZipCode($form->get('expZipCode')->getData());
                $annonce->setStatutLivraison(false);
                $expRelID = $form->get('expRelId')->getData();
                if ($expRelID != '') {
                    $annonce->setExpRelId($form->get('expRelId')->getData());
                }
                $annonce->setStatus("not sold");
                $OriginPrice = $annonce->getPriceOrigin();
                $fixFees = 0.7;
                $percentPrice = $OriginPrice * 0.12;
                $totalPrice = round($OriginPrice + $fixFees + $percentPrice, 3);


                $modPrice = fmod($totalPrice, 1); //Pour obtenir le rest et déterminer l'arrondi correspondant avec les conditions ci dessous
                $preFinal = "";


                if ($modPrice > 0 && $modPrice < 0.5) {
                    $preFinal = round($totalPrice, 0) + 0.5;
                } else if ($modPrice >= 0.5 && $modPrice < 1) {
                    $preFinal = round($totalPrice, 0);
                }
                $totalFees = $preFinal - $totalPrice + $fixFees + $percentPrice;
                $commissionSite = $totalFees - (0.018 * $preFinal + 0.18);
                $a = array($preFinal, $totalPrice, $totalFees, $commissionSite);
                $annonce->setPriceTotal($preFinal);

                $entityManager->persist($annonce);
                $entityManager->flush();
                // do anything else you need here, like send an email

                return $this->redirectToRoute("annonce_ajouter");
            }
        }

        return $this->render('annonce/index.html.twig', compact('form', 'annonce'));
    }


    //Fonction pour supprimer une annonce manuellement.
    #[Route('/supprimer/{annonce}', name: '_supprimer')]
    public function supprimer(
        EntityManagerInterface $entityManager,
        Annonce                $annonce,
    ): Response
    {
        $entityManager->remove($annonce);
        $entityManager->flush();
        $this->addFlash('succes', "L'annonce a bien été supprimé");
        return $this->redirectToRoute('annonce');
    }

    // Fonction supprime automatiquement les annonces lorsque celle-ci est expirée.
    public function isExpired(
        AnnonceRepository      $annonceRepository,
        EntityManagerInterface $entityManager,
    )
    {
        $annonces = $annonceRepository->findAll();
        $today = new \DateTime();

        foreach ($annonces as $annonce) {
            if (!$annonce->isSold()) {
                $diff = $annonce->getDateExpiration()->diff($today);

                if ($diff->invert == 0) {
                    $annonceRepository->remove($annonce);
                    $entityManager->flush();
                }
            } else {
                $todayStr = $annonce->getDateExpiration()->format("Y/m/d");
                $todayTime = strtotime($todayStr);
                $dateTime = strtotime("+5 day", $todayTime);

                if ($today->format("Y-m-d") == date("Y-m-d", $dateTime)) {
                    $entityManager->remove($annonce);
                    $entityManager->flush();
                }
            }
        }
    }
}
