<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Form\SearchAnnonceType;
use App\Repository\AnnonceRepository;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

// Controller permettant l'accès aux pages principales de la barre de navigation.
#[Route('/', name: 'home')]// utilisation des préfixes.
class HomeController extends AbstractController
{

    // Route de la page d'accueil.
    #[Route('/', name: '_homepage')]
    public function homepage(
        AnnonceRepository      $annonceRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        // Appel de la fonction permettant de supprimer les anciennes annonces en base de données.
        $this->deleteOldAnnonce($annonceRepository, $entityManager);

        // Création du formulaire de recherche.
        $form = $this->createForm(SearchAnnonceType::class);
        $form->handleRequest($request);

        // Récupération uniquement des annonces non-vendu.
        $annonces = $annonceRepository->findBy(array('sold' => 0));

        // Duplication de cette même variable pour une nouvelle qui sera uniquement utilisé pour le script.
        $annoncesForScript = $annonces;

        // Si le formulaire est envoyé et valide à la fois.
        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération de la recherche de l'utilisateur.
            $title = $form->get('title')->getData();

            // Redirection sur la page des annonces avec la recherche de l'utilisateur.
            return $this->redirectToRoute("home_annonces", compact('title'));
        }
        return $this->renderForm('home/homepage.html.twig', compact('annonces','annoncesForScript', 'form'));
    }

    // Route de la page annonces.
    #[Route('/annonces', name: '_annonces')]
    public function annonces(
        AnnonceRepository      $annonceRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        // Appel de la fonction permettant de supprimer les anciennes annonces en base de données.
        $this->deleteOldAnnonce($annonceRepository, $entityManager);

        // Création du formulaire de recherche.
        $form = $this->createForm(SearchAnnonceType::class);
        $form->handleRequest($request);

        // Récupération uniquement des annonces non-vendu qui sera utilisé pour le script.
        $annonces = $annonceRepository->findBy(array('sold' => 0));

        // Duplication de cette même variable pour une nouvelle qui sera uniquement utilisé pour le script.
        $annoncesForScript = $annonces;

        // Total de toutes les annonces non-vendus.
        $totalAnnonces = count($annonces);

        // Si le formulaire n'est pas envoyé ce qui est le cas dans le premier passage de cette fonction.
        if (!$form->isSubmitted()) {

            // Si la recherche effectuer par l'utilisateur dans la homepage existe.
            if (isset($_GET['title'])) {

                // Récupération de cette recherche.
                $title = $_GET['title'];

                // Utilisation de cette recherche dans le formulaire où il sera placé au même endroit dans cette page.
                $form->get('title')->setData($title);

                // Récupération des annonces non-vendu dont le titre correspond à la recherche de l'utilisateur.
                $annonces = $annonceRepository->findBy(array('sold' => 0, 'title' => $title));
            }

            // Si le formulaire est envoyé.
        } else {

            // Récupération de la recherche de l'utilisateur.
            $title = $form->get('title')->getData();

            // Récupération des annonces non-vendu dont le titre correspond à la recherche de l'utilisateur.
            $annonces = $annonceRepository->findBy(array('sold' => 0, 'title' => $title));
        }

        // Total de toutes les annonces non-vendus correspondant ou non à la recherche de l'utilisateur selon le cas.
        $totalAnnoncesFound = count($annonces);

        // Initialisation d'"aucune annonce" à faux.
        $noAnnonce = false;

        // Si il n'y a pas d'annonce trouvé.
        if ($totalAnnoncesFound == 0) {

            // Récupération des annonces non-vendu.
            $annonces = $annonceRepository->findBy(array('sold' => 0));

            // Si l'utilisateur à saisie une recherche
            if ($form->get('title')->getData() != '') {

                // Utilisation du message d'erreur pour indiquer qu'aucune annonce n'a été trouvé.
                $noAnnonce = true;
            }

            // Sinon si l'utilisateur n'a rien saisie pour la recherche.
        } elseif ($form->get('title')->getData() == '') {

            // Le nombre d'annonces trouvées est de 0.
            $totalAnnoncesFound = 0;
        }

        return $this->renderForm('home/annonces.html.twig', compact(
            'annonces',
            'totalAnnonces',
            'totalAnnoncesFound',
            'annoncesForScript',
            'form',
            'noAnnonce'
        ));
    }

    // Route de la page À propos.
    #[Route('/about', name: '_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    // Route de la page contact.
    #[Route('/contact', name: '_contact')]
    public function contact(
        Request         $request,
        MailerInterface $mailer
    ): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();
            $message = (new Email())
                ->from($contactFormData['email'])
                ->to('plantetflower@gmail.com')
                ->subject('Message de contact')
                ->text(
                    'Expéditeur : ' . $contactFormData['email'] . \PHP_EOL .
                    $contactFormData['message'], 'text/plain'
                );
            $mailer->send($message);
            $this->addFlash('success', 'Votre message a été envoyé');
            return $this->redirect('/contact');
        }

        return $this->renderForm('home/contact.html.twig',
            compact('form'));
    }

    // Route de la page FAQ.
    #[Route('/faq', name: '_faq')]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }

    // Route de la page des mentions légales.
    #[Route('/mentionslegales', name: '_mentionsLegales')]
    public function mentionsLegales(): Response
    {
        return $this->render('home/mentionslegales.html.twig');
    }

    // Route de la page des cgu/cgv.
    #[Route('/cgu', name: '_cgu')]
    public function cgu(): Response
    {
        return $this->render('home/cgu.html.twig');
    }

    // Fonction permettant de supprimer les anciennes annonces en base de données.
    public function deleteOldAnnonce(
        AnnonceRepository      $annonceRepository,
        EntityManagerInterface $entityManager,
    ): void
    {
        // Récupération de toutes les annonces.
        $annoncesAll = $annonceRepository->findAll();

        // Création de la variable "maintenant".
        $now = new \DateTime();

        // Boucle sur toutes les annonces.
        foreach ($annoncesAll as $annonce) {

            // Si le produit de l'annonce n'est pas encore vendu.
            if (!$annonce->isSold()) {

                // Boolean vrai si différence de temps entre l'expiration de l'annonce et maintenant.
                $diff = $annonce->getDateExpiration()->diff($now);

                // Retire l'annonce si elle est antérieure à maintenant et si l'annonce n'est pas vendu.
                if ($diff->invert == 0 && $annonce->getStatus() == "not sold") {
                    // Récupération des images de l'annonce
                    $actualImages = $annonce->getImages();
                    foreach ($actualImages as $image) {
                        $entityManager->remove($image);
                        $entityManager->flush();
                    }

                    $annonceRepository->remove($annonce);
                    $entityManager->flush();
                }
            }
        }
    }
}
