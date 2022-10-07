<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceForm;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/annonce', name: 'annonce')]
class AnnonceController extends AbstractController
{

    //  Fonction pour afficher une annonce
    #[Route('/annonce/{annonceId}', name: '_afficher', requirements: ['annonceId' => '\d+'])]
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

    // Fonction pour ajouter une annonce
    #[Route('/create', name: '_ajouter')]
    public function createAnnonce(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {

        // Création d'une nouvelle annonce
        $annonce = new Annonce();

        // Création d'un formulaire d'annonce
        $form = $this->createForm(AnnonceForm::class, $annonce);
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide à la fois
        if ($form->isSubmitted() && $form->isValid()) {

            // Envoi de l'annonce et du formulaire à une fonction permettant d'enregistrer en base de données
            $this->dataSave($annonce, $entityManager, $form);

            // Redirection sur l'affichage de cette annonce
            return $this->redirectToRoute("annonce_afficher", [
                'annonceId' => $annonce->getId()
            ]);

        }
        // Redirection vers la page de création d'annonce
        return $this->render('annonce/createAnnonce.html.twig', [
            'annonceForm' => $form->createView(),
        ]);
    }


    // Fonction pour éditer une annonce
    #[Route('/edit/{annonceId}', name: '_editer', requirements: ['annonceId' => '\d+'])]
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
        $form = $this->createForm(AnnonceForm::class, $annonce);
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide à la fois
        if ($form->isSubmitted() && $form->isValid()) {

            // Envoi de l'annonce et du formulaire à une fonction permettant d'enregistrer en base de données
            $this->dataSave($annonce, $entityManager, $form);

            // Redirection sur l'affichage de cette annonce
            return $this->redirectToRoute("annonce_afficher", [
                'annonceId' => $annonce->getId()
            ]);
        }

        // Redirection vers la page d'édition de l'annonce
        return $this->render('annonce/editAnnonce.html.twig', [
            'annonceForm' => $form->createView(),
            'images' => $annonce->getImages()
        ]);
    }

    // Fonction pour supprimer une annonce manuellement.
    #[Route('/supprimer/{annonceId}', name: '_supprimer', requirements: ['annonceId' => '\d+'])]
    public function deleteAnnonce(
        AnnonceRepository      $annonceRepository,
        EntityManagerInterface $entityManager,
                               $annonceId,
    ): Response
    {

        // Récupération de l'annonce par son Id
        $annonce = $annonceRepository->find($annonceId);

        // Si l'utilisateur est bien celui qui a créée l'annonce
        if ($this->getUser() === $annonce->getUser()) {

            // Suppression de l'annonce
            $entityManager->remove($annonce);
            $entityManager->flush();
            $this->addFlash('succes', "L'annonce a bien été supprimé");

        } else {

            $this->addFlash('error', 'Vous ne pouvez pas supprimer les annonces des autres utilisateurs');

        }

        // Redirection vers le profil de l'utilisateur
        return $this->redirectToRoute("user_profil",
        );
    }

    // Fonction permettant d'enregistrer un formulaire d'annonce en base de données
    public function dataSave(
        Annonce                $annonce,
        EntityManagerInterface $entityManager,
                               $form
    ): void
    {

        // Création de la variable image
        $images = $form->get('images')->getData();

        // Si trop d'images
        if (count($images) > 3) {
            $form->addError(new FormError("Trop d'images"));

            // Si aucune image
        } elseif (count($images) < 1) {
            $form->addError(new FormError("Vous devez upload au minimum 1 image"));
        }

//            $currentImages = $annonce->getImages();
//            if((count($currentImages) + count($images)) > 3)
//            {
//                $imagesToDelete = count($currentImages) + count($images) - 3;
//                for ($i = 0; $i < $imagesToDelete; $i++)
//                {
//                    $annonce->removeImage($currentImages[$i]);
//                }
//            }

        // ajout des images de l'utilisateur

//                foreach ($images as $image) {
//                    $fichier = md5(uniqid()) . '.' . $image->guessExtension();

//                    //on copie le fichier dans le dossier uploads
//                    $image->move(
//                        $this->getParameter('annonces_directory'),
//                        $fichier
//                    );
//                    $img = new Image();
//                    $img->setName($fichier);
//                    $annonce->addImage($img);
//                }

        // Récupération des réponses données par l'utilisateur

        // Variables créée pour certaines réponses de l'utilisateur nécessitant une condition
        $description = $annonce->getDescription();
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

        // Si l'annonce fonctionne par expédition
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


        // Calcul du prix total

        // Récupération du prix initial (celui de l'utilisateur)
        $originPrice = $annonce->getPriceOrigin();


        // Déclaration des valeurs ajoutées

        // Frais fixes
        $fixedFee = 0.7;

        // Pourcentage de marge
        $marginPercentage = 0.12;

        // Calcul de la marge
        $margin = $originPrice * $marginPercentage;

        // Calcul du prix taxé arrondi au 3e chiffre après la virgule
        $taxedPrice = round($originPrice + $fixedFee + $margin, 3);


        // Arrondissement à la demi-unité supérieure

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
}