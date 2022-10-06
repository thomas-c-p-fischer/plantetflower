<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\CreateAnnonceType;
use App\Repository\AnnonceRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/annonce', name: 'annonce')]
class AnnonceController extends AbstractController
{

    //  fonction pour afficher une annonce
    #[Route('/annonce/{annonceId}', name: '_afficher', requirements: ['annonceId' => '\d+'])]
    public function showAnnonce(AnnonceRepository $annonceRepository, $annonceId): Response
    {
        // récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($annonceId);
        // récupération de l'id de l'auteur de l'annonce
        $idAuthorAnnonce = $annonce->getUser()->getId();
        // récupération des annonces de l'auteur concernant l'annonce actuel
        $annoncesAuthor = $annonceRepository->findBy(array('user' => $idAuthorAnnonce));
        // total des annonces de l'auteur concernant l'annonce actuel
        $totalAnnoncesAuthor = count($annoncesAuthor);

        return $this->render('annonce/annonce.html.twig', [
            'annonce' => $annonce,
            'annoncesAuthor' => $annoncesAuthor,
            'totalAnnoncesAuthor' => $totalAnnoncesAuthor
        ]);
    }

    // fonction pour ajouter une annonce
    #[Route('/createAnnonce', name: '_ajouter')]
    public function createAnnonce(
        Request                $request,
        EntityManagerInterface $entityManager,
        UploadService          $uploadService,
    ): Response
    {
        $annonce = new Annonce();
        $monImage = new Image();
        // création du formulaire de la création d'une annonce
        $form = $this->createForm(CreateAnnonceType::class, $annonce);
        $form->handleRequest($request);

        // si le formulaire est envoyé et valide à la fois
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les images transmises
            $images = $form->get('images')->getData();

            // On boucle sur les images
            foreach ($images as $image) {
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                // On crée l'image dans la base de données
                $img = new Image();
                $img->setName($fichier);
                $annonce->addImage($img);

                if (count($images) > 3) {
                    $form->addError(new FormError("Trop d'images"));
                } elseif (count($images) < 1) {
                    $form->addError(new FormError("Vous devez upload au minimum 1 image"));
                }
                // récupérer les données du formulaire
                $description = $annonce->getDescription();
                $shipement = $annonce->isShipement();
                $plantPot = $annonce->isPlantPot();
                $preUpperVille = $annonce->getVille();
                $postUpperVille = strtoupper($preUpperVille);

                if ($description) {
                    $annonce->setDescription($description);
                }
                if ($plantPot) {
                    $annonce->setPlantPot(true);
                } else {
                    $annonce->setPlantPot(false);
                }
                if ($shipement) {
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
                $annonce->setExpAdress($form->get('expAdress')->getData());
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


                $modPrice = fmod($totalPrice, 1);
                //Pour obtenir le reste et déterminer l'arrondi correspondant avec les conditions ci-dessous
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

                // redirection sur l'affichage de cette annonce
                return $this->redirectToRoute("annonce_afficher",
                    ['annonceId' => $annonce->getId(),
                        'img' => $img,
                    ]);
            }
        }

        // redirection vers la page de création d'annonce
        return $this->render('annonce/createAnnonce.html.twig', [
            'CreateAnnonceForm' => $form->createView(),


        ]);
    }


    //Fonction pour supprimer une annonce manuellement.
    #[Route('/supprimer/{annonceId}', name: '_supprimer', requirements: ['annonceId' => '\d+'])]
    public function deleteAnnonce(
        AnnonceRepository      $annonceRepository,
        EntityManagerInterface $entityManager,
                               $annonceId,
    ): Response
    {

        // récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($annonceId);

        // si l'utilisateur est bien celui qui a créée l'annonce
        if ($this->getUser() === $annonce->getUser()) {

            // suppression de l'annonce
            $entityManager->remove($annonce);
            $entityManager->flush();
            $this->addFlash('succes', "L'annonce a bien été supprimé");

        } else {

            $this->addFlash('error', 'Vous ne pouvez pas supprimer les annonces des autres utilisateurs');

        }

        // redirection vers le profil de l'utilisateur
        return $this->redirectToRoute("user_profil",
        );
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