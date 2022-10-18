<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\SearchAnnonceType;
use App\Repository\AnnonceRepository;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//controller permettant l'accès aux pages principales de la barre de navigation
#[Route('/', name: 'home')]//utilisation des préfixes
class HomeController extends AbstractController
{

    //route de la page d'accueil
    #[Route('/', name: '_homepage')]
    public function homepage(
        AnnonceRepository $annoncesRepository,
        Request           $request,
        ImageRepository   $imageRepository,

    ): Response
    {
        $limit = 10;
        $page = (int)$request->query->get("page", 1);

        $total = $annoncesRepository->getTotalAnnonces();
        $lastAnnonces = $annoncesRepository->getLastAnnonces();
        $images = $imageRepository->findAll();
        $annoncesAll = $annoncesRepository->findAll();
        $form = $this->createForm(SearchAnnonceType::class);
        $form->handleRequest($request);
        $annonces = [];

        if ($form->isSubmitted() && $form->isValid()) {
            // On recherche les annonces correspondant aux mots clés
            $title = $form->get('title')->getData();

            if ($title != "") {
                $annonces = $annoncesRepository->findBy(['title' => $title]);
            } else {
                $annonces = $annoncesRepository->getPaginatedAnnonces($page, $limit);
            }
        }
        return $this->render('home/homepage.html.twig', [
            'controller_name' => 'HomeController',
            'annonces' => $annonces,
            'lastAnnonces' => $lastAnnonces,
            'annoncesAll' => $annoncesAll,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'form' => $form->createView(),
            'images' => $images
        ]);
    }

    //route de la page annonces
    #[Route('/annonces', name: '_annonces')]
    public function annonces(
        AnnonceRepository $annonceRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        // récupération de toutes les annonces
        $annonces = $annonceRepository->findAll();

        // création de la variable "maintenant"
        $now = new \DateTime();

        // boucle toutes les annonces
        foreach ($annonces as $annonce) {

            // si le produit de l'annonce n'est pas encore vendu
            if (!$annonce->isSold()) {

                // boolean vrai si différence de temps entre l'expiration de l'annonce et maintenant
                $diff = $annonce->getDateExpiration()->diff($now);

                // retire l'annonce si elle est antérieure à maintenant
                if ($diff->invert == 0) {
                    $annonceRepository->remove($annonce);
                    $entityManager->flush();
                }
            }
        }

        // nouvelle récupération des annonces après avoir retiré celles éventuellement expirées
        $annonces = $annonceRepository->findAll();

        // total de toutes les annonces
        $totalAnnonces = count($annonces);
        return $this->render('home/annonces.html.twig', compact('annonces', 'totalAnnonces'));
    }

    //route de la page À propos
    #[Route('/about', name: '_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    //route de la page contact
    #[Route('/contact', name: '_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }

    //route de la page FAQ
    #[Route('/faq', name: '_faq')]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }

    //route de la page des mentions légales
    #[Route('/mentionslegales', name: '_mentionsLegales')]
    public function mentionsLegales(): Response
    {
        return $this->render('home/mentionslegales.html.twig');
    }

    //route de la page des cgu/cgv
    #[Route('/cgu', name: '_cgu')]
    public function cgu(): Response
    {
        return $this->render('home/cgu.html.twig');
    }
}
