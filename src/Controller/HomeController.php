<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//controller permettant l'accès aux pages principales de la barre de navigation
#[Route('/', name: 'home_')]//utilisation des préfixes
class HomeController extends AbstractController
{

    //route de la page d'accueil
    #[Route('/', name: '_homepage')]
    public function homepage(): Response
    {
        return $this->render('home/homepage.html.twig');
    }

    //route de la page annonces
    #[Route('/annonces', name: '_annonces')]
    public function annonces(): Response
    {
        return $this->render('home/annonces.html.twig');
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
