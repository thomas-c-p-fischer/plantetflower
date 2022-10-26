<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceForm;
use App\Entity\Image;
use App\Form\ModeRemiseFormType;
use App\Repository\AnnonceRepository;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/annonce', 'annonce')]
class AnnonceController extends AbstractController
{
    //  fonction pour afficher une annonce
    #[Route('/{annonceId}', '_afficher', requirements: ['annonceId' => '\d+'])]
    public function showAnnonce(AnnonceRepository $annonceRepository, $annonceId): Response
    {
        // Récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($annonceId);
        // Récupération de l'id de l'auteur de l'annonce
        $idAuthorAnnonce = $annonce->getUser()->getId();
        // Récupération des annonces de l'auteur concernant l'annonce actuel
        $annoncesAuthor = $annonceRepository->findBy(array('user' => $idAuthorAnnonce));
        // Total des annonces de l'auteur concernant l'annonce actuel
        $totalAnnoncesAuthor = count($annoncesAuthor);

        // Redirection sur l'affichage de cette annonce
        return $this->render('annonce/annonce.html.twig', [
            'annonce' => $annonce,
            'annoncesAuthor' => $annoncesAuthor,
            'totalAnnoncesAuthor' => $totalAnnoncesAuthor
        ]);
    }

    // fonction pour ajouter une annonce
    #[Route('/ajouter', name: '_ajouter')]
    public function createAnnonce(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        // Création d'une nouvelle annonce
        $annonce = new Annonce();
        // Création d'un formulaire d'annonce
        $annonceForm = $this->createForm(AnnonceForm::class, $annonce);
        $monImage = new Image();
        // création du formulaire de la création d'une annonce
        $annonceForm->handleRequest($request);
        $images = $annonce->getImages();
        // Si le formulaire est envoyé et valide à la fois
        if ($annonceForm->isSubmitted() && $annonceForm->isValid()) {
            // Envoi de l'annonce et du formulaire à une fonction permettant d'enregistrer en base de données
            $this->dataSave($annonce, $entityManager, $annonceForm);
            // Redirection sur l'affichage de cette annonce
            return $this->redirectToRoute("annonce_afficher", ['annonceId' => $annonce->getId()]);
        }

        // Redirection vers la page d'édition de l'annonce
        return $this->renderForm('annonce/createAnnonce.html.twig', compact('annonceForm', 'images'));
    }

    // Fonction pour éditer une annonce
    #[Route('/edit/{annonceId}', '_editer', requirements: ['annonceId' => '\d+'])]
    public function editAnnonce(
        AnnonceRepository      $annonceRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
                               $annonceId
    ): Response
    {
        // Récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($annonceId);
        // Si l'utilisateur n'est pas celui qui a créé l'annonce
        if ($this->getUser() !== $annonce->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier les annonces des autres utilisateurs');
            // Redirection sur l'affichage de cette annonce
            return $this->redirectToRoute("annonce_afficher", [
                'annonceId' => $annonce->getId()
            ]);
        }
        // Création d'un formulaire d'annonce
        $annonceForm = $this->createForm(AnnonceForm::class, $annonce);
        $annonceForm->handleRequest($request);
        $actualImages = $annonce->getImages();
        // Si le formulaire est envoyé et valide à la fois
        if ($annonceForm->isSubmitted() && $annonceForm->isValid()) {
            // On récupère les nouvelles images transmises
            $newImages = array(
                $annonceForm->get('image_0')->getData(),
                $annonceForm->get('image_1')->getData(),
                $annonceForm->get('image_2')->getData()
            );
            $nbNewImages = 0;
            // On boucle sur les nouvelles images
            foreach ($newImages as $image) {
                // Si l'image existe
                if ($image) {
                    // Le nombre de nouvelles images est incrémenté à +1
                    $nbNewImages++;
                }
            }
            $nbActualImages = count($actualImages);
            $nbTotalImages = $nbActualImages + $nbNewImages;
            // Si aucune image
            if ($nbTotalImages < 1) {
                $annonceForm->addError(new FormError("Vous devez importer au minimum 1 image"));
                // Si trop d'images
            } elseif ($nbTotalImages > 3) {
                $annonceForm->addError(new FormError("Vous ne pouvez importer que 3 images pour une annonce"));
            } else {
                // Envoi de l'annonce et du formulaire à une fonction permettant d'enregistrer en base de données
                $this->dataSave($annonce, $entityManager, $annonceForm);
                // Redirection sur l'affichage de cette annonce
                return $this->redirectToRoute("annonce_afficher", [
                    'annonceId' => $annonce->getId()
                ]);
            }
        }

        // Redirection vers la page d'édition de l'annonce
        return $this->renderForm('annonce/editAnnonce.html.twig', compact('annonce', 'annonceForm', 'actualImages'));
    }

    // Fonction pour supprimer l'image d'une annonce
    #[Route('/supprimerImage/{imageId}', name: '_supprimerImage', requirements: ['imageId' => '\d+'])]
    public function deleteAnnonceImg(
        ImageRepository        $ImageRepository,
        AnnonceRepository      $annonceRepository,
        EntityManagerInterface $entityManager,
                               $imageId
    ): Response
    {
        // Récupération de l'image par son Id
        $image = $ImageRepository->find($imageId);
        // Récupération de l'annonce par l'image
        $annonce = $annonceRepository->find($image->getAnnonceId());
        // Si l'utilisateur n'est pas celui qui a créé l'annonce
        if ($this->getUser() !== $annonce->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier les annonces des autres utilisateurs');
            // Redirection sur l'affichage de cette annonce
            return $this->redirectToRoute("annonce_afficher", [
                'annonceId' => $annonce->getId()
            ]);
        } else if (count($annonce->getImages()) > 1) {
            $entityManager->remove($image);
            $entityManager->flush();
            $this->addFlash('succes', "L'image a bien été supprimé");
        }

        // Redirection vers la page d'édition de l'annonce
        return $this->redirectToRoute("annonce_editer", ['annonceId' => $annonce->getId()]);
    }

    //Fonction pour supprimer une annonce manuellement.
    #[Route('/supprimer/{annonceId}', '_supprimer', requirements: ['annonceId' => '\d+'])]
    public function deleteAnnonce(
        AnnonceRepository      $annonceRepository,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,
                               $annonceId,
    ): Response
    {
        // Récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($annonceId);
        // Si l'utilisateur est bien celui qui a créé l'annonce
        if ($this->getUser() === $annonce->getUser()) {
            // Récupération des images de l'annonce
            $actualImages = $annonce->getImages();
            foreach ($actualImages as $image) {
                $entityManager->remove($image);
                $entityManager->flush();
            }
            // Suppression de l'annonce
            $entityManager->remove($annonce);
            $entityManager->flush();
            $this->addFlash('success', "L'annonce a bien été supprimé");
        } else {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer les annonces des autres utilisateurs');
        }
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);

        // Redirection vers le profil de l'utilisateur
        return $this->redirectToRoute("user_profil", ['id' => $userConnect->getId()]);
    }

    // Fonction permettant d'enregistrer un formulaire d'annonce en base de données
    public function dataSave(
        Annonce                $annonce,
        EntityManagerInterface $entityManager,
                               $form
    ): void
    {
        // On récupère les nouvelles images transmises
        $newImages = array(
            $form->get('image_0')->getData(),
            $form->get('image_1')->getData(),
            $form->get('image_2')->getData()
        );
        // On boucle sur les nouvelles images
        foreach ($newImages as $image) {
            // Si l'image existe
            if ($image) {
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
            }
        }
        // Récupération des réponses données par l'utilisateur :
        // Variables créée pour certaines réponses de l'utilisateur nécessitant une condition
        $description = $annonce->getDescription();
        $handDelivery = $annonce->isHandDelivery();
        $shipement = $annonce->isShipement();
        $plantPot = $annonce->isPlantPot();
        $expRelID = $form->get('expRelId')->getData();
        // Si une description est existante
        if ($description) {
            $annonce->setDescription($description);
        }
        // Si un pôt est existant
        if ($plantPot) {
            $annonce->setPlantPot(true);
        } else {
            $annonce->setPlantPot(false);
        }
        // Si l'annonce fonctionne par remise en main propre
        if ($handDelivery) {
            $annonce->setHandDelivery(true);
        } else {
            $annonce->setHandDelivery(false);
        }
        // Si l'annonce fonctionne par livraison
        if ($shipement) {
            $annonce->setShipement(true);
        } else {
            $annonce->setShipement(false);
        }
        // Si l'annonce contient un ID du point de relai pour l'expédition
        if ($expRelID != '') {
            $annonce->setExpRelId($form->get('expRelId')->getData());
        }
        // Prix initial
        $annonce->setPriceOrigin($form->get('priceOrigin')->getData());
        // Date d'expiration
        $annonce->setDateExpiration($form->get('dateExpiration')->getData());
        // Titre
        $annonce->setTitle($form->get('title')->getData());
        // Catégorie
        $annonce->setCategory($form->get('category')->getData());
        // Poids de la plante
        $annonce->setPoids($form->get('poids')->getData());
        // Vendu (boolean)
        $annonce->setSold(false);
        $annonce->setVille(strtoupper($form->get('ville')->getData()));
        // L'utilisateur actuel
        $annonce->setUser($this->getUser());
        // Date et de l'heure actuel
        $annonce->setCreatedAt(new \DateTime());
        // L'adresse
        $annonce->setExpAdress($form->get('expAdress')->getData());
        // Code postal
        $annonce->setExpZipCode($form->get('expZipCode')->getData());
        // Le statut de livraison est initialisé à faux
        $annonce->setStatutLivraison(false);
        // Statut initialisé à "non vendu"
        $annonce->setStatus("not sold");
        // Calcul du prix total:
        // Récupération du prix initial (celui de l'utilisateur)
        $originPrice = $annonce->getPriceOrigin();
        // Déclaration des valeurs ajoutées:
        // Frais fixes
        $fixedFee = 0.7;
        // Pourcentage de marge
        $marginPercentage = 0.12;
        // Calcul de la marge
        $margin = $originPrice * $marginPercentage;
        // Calcul du prix taxé arrondi au 3e chiffre après la virgule
        $taxedPrice = round($originPrice + $fixedFee + $margin, 3);
        // Arrondissement à la demi-unité supérieure
        //????????????????????????????????????
        // Récupération du modulo du prix taxé
        $moduloTaxedPrice = fmod($taxedPrice, 1);
        // Initialisation de la variable du prix total
        $totalPrice = "";
        // Si le modulo du prix taxé est entre 0 et 0,5
        if ($moduloTaxedPrice > 0 && $moduloTaxedPrice < 0.5) {
            // Arrondissement du prix total à l'entier supérieur ajouté à 0,5
            $totalPrice = round($taxedPrice, 0) + 0.5;
            // Si le modulo du prix taxé est supérieur ou égal à 0,5 et inférieur à 1
        } else if ($moduloTaxedPrice >= 0.5 && $moduloTaxedPrice < 1) {
            // Arrondissement du prix total à l'entier supérieur
            $totalPrice = round($taxedPrice, 0);
        }
//        $totalFees = $totalPrice - $taxedPrice + $fixedFee + $marginPercentage;
//        $commissionSite = $totalFees - (0.018 * $totalPrice + 0.18);
//        $a = array($totalPrice, $taxedPrice, $totalFees, $commissionSite);
        // Assignation du prix total à l'annonce
        $annonce->setPriceTotal($totalPrice);
        // Envoie des informations en base de donnée
        $entityManager->persist($annonce);
        $entityManager->flush();
    }

    //Controller dans lequel on choisit le mode de remise (main propre ou mondialRelay), (étape 1/2)
    #[Route('/modeDeRemise/{id}', '_modeDeRemise')]
    public function modeDeRemise(
        AnnonceRepository      $annonceRepository,
        Request                $request,
        EntityManagerInterface $em,
                               $id,
    ): Response
    {
        //Récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($id);
        //Création de la form
        $form = $this->createForm(ModeRemiseFormType::class);
        $form->handleRequest($request);
        //Déclaration des variables avec les données nécessaires
        $annoncePoids = $annonce->getPoids();
        $buyerDelivery = $annonce->isBuyerDelivery();
        //Variable qui va changer le prix total si mondial relay est choisi par rapport au poids
        $prixPoids = 0;
        if ($annoncePoids == "0g - 500g") {
            $prixPoids = 5.50;
        } elseif ($annoncePoids == "501g - 1kg") {
            $prixPoids = 6;
        } elseif ($annoncePoids == "1.001kg - 2kg") {
            $prixPoids = 7.50;
        } elseif ($annoncePoids == "2.001kg - 3kg") {
            $prixPoids = 8;
        }

        //Si le formulaire est envoyé et valide à la fois
        if ($form->isSubmitted() && $form->isValid()) {
            //Si la checkBox mondial relay n'est pas cochée alors :
            if ($form->get('mondialRelay')->getData() != 'checked') {
                //le paramètre de l'entité annonce buyer_delivery sera false
                $annonce->setBuyerDelivery(false);
                //sinon si la checkbox mondial relay est cochée
            } else {
                //alors le paramètre de l'entité Annonce buyer_delivery sera true
                $annonce->setBuyerDelivery(true);
                //Recuperation de l'ID de point de relais choisi afin de créer l'étiquette lorsque le paiement est accepté
                $idRelais = $form->get('relais')->getData();
                //Cette id est placé en session afin de la récupérée dans le controller de callBack "PaiementController" dans la methode de redirection.
                $_SESSION['idRelais'] = $idRelais;
            }
            //Comme buyer_delivery est un paramètre de l'entité Annonce, il nous faut persist et flush pour implémenter
            //les changements en BDD
            $em->persist($annonce);
            $em->flush($annonce);
            //Et après on redirige vers le controller de l'étape 2/2 du paiement
            return $this->redirectToRoute('annonce_paiement', compact('id', 'buyerDelivery'));
        }

        return $this->renderForm("annonce/modeDeRemise.html.twig",
            compact(
                'annonce',
                'prixPoids',
                'form',
                'id',
            ));
    }

    //Controller pour le paiement d'une annonce (étape2/2)
    #[Route('/paiement/{id}', '_paiement')]
    public function paiement(
        MangoPayService   $service,
        UserRepository    $userRepository,
        AnnonceRepository $annonceRepository,
                          $id,
    ): Response
    {
        //Récupération de l'annonce par son Id
        $annonce = $annonceRepository->findOneBy(["id" => $id]);
        //Création de l'URL de retour sur lequel la data est renvoyée
        $returnURL = $this->generateUrl(
            'paiement_updateRegistrationCard',
            ['id' => $annonce->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        //Récupération du titre de l'annonce pour affichage sur la page
        $annonceTitle = $annonce->getTitle();
        //Stockage en variable des données utiles pour le récapitulatif de la somme à payer
        $annoncePriceOrigin = $annonce->getPriceOrigin();
        $annoncePriceTotal = $annonce->getPriceTotal();
        $annoncePoids = $annonce->getPoids();
        $buyerDelivery = $annonce->isBuyerDelivery();
        //Variable qui va changer le prix total si mondial relay est choisi par rapport au poids
        $prixPoids = 0;
        if ($annoncePoids == "0g - 500g") {
            $prixPoids = 5.50;
        } elseif ($annoncePoids == "501g - 1kg") {
            $prixPoids = 6;
        } elseif ($annoncePoids == "1.001kg - 2kg") {
            $prixPoids = 7.50;
        } elseif ($annoncePoids == "2.001kg - 3kg") {
            $prixPoids = 8;
        }
        //Récupération de l'utilisateur connecté
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        //Utilisation de la méthode du service pour créer une nouvelle carte et insertion des données pré-requises
        //dans le formulaire sans les afficher
        $cardRegistration = $service->createCardRegistration($userConnect);
        $accessKey = $cardRegistration->AccessKey;
        //On définit une variable dans laquelle on stock la PreregistrationData
        $preregistrationData = $cardRegistration->PreregistrationData;
        //On définit une variable dans laquelle on stock la CardRegistrationURL
        $cardRegistrationUrl = $cardRegistration->CardRegistrationURL;

        return $this->render("annonce/annoncePaiement.html.twig",
            compact(
                'annonce',
                'accessKey',
                'preregistrationData',
                'returnURL',
                'cardRegistrationUrl',
                'annoncePriceOrigin',
                'annoncePriceTotal',
                'annoncePoids',
                'prixPoids',
                'buyerDelivery',
                'annonceTitle',
            ));
    }
}