<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Entity\User;
use App\Form\InformationFormType;
use App\Form\UserFormType;
use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use Doctrine\ORM\EntityManagerInterface;
use MangoPay\KycDocumentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', 'user')]
class UserProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: '_profil')]
    public function profil(
        Request           $request,
        MangoPayService   $service,
        UserRepository    $userRepository,
        AnnonceRepository $annonceRepository,
    ): Response
    {

        //Récupération de l'utilisateur connecté par son Email.
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        //Création du formulaire d'ajout d'IBAN.
        $informationForm = $this->createForm(InformationFormType::class);
        $informationForm->handleRequest($request);
        //Récupération de toutes les annonces achetée par cet utilisateur.
        $annonces = $annonceRepository->findBy(array('acheteur' => $mail));
        //Récupération de la donnée sans stockage en BDD
        $iban = $informationForm['IBAN']->getData();
        $document = KycDocumentType::IdentityProof;
        if ($informationForm->isSubmitted() && $informationForm->isValid()) {
            //Si le formulaire est valide, alors on utilise la méthode du service pour ajouter l'iban au compte mangopay associé par l'id.
            $service->createBankAccount($userConnect, $iban);
            //Utilisation de la méthode du service pour créer des KYC documents.
            $KYC = $service->createKYCDocument($userConnect, $document);
            $verso = $informationForm['KYCverso']->getData();
            $recto = $informationForm['KYCrecto']->getData();
            $kycDoc = $service->createKYCPage($userConnect, $KYC, $recto, $verso);
            $service->submitKYCDocument($userConnect, $kycDoc);

        }
        return $this->renderform('user_profil/userProfil.html.twig', compact('informationForm', 'mail', 'annonces', 'userConnect'));
    }


    #[Route('/edit/{id}', name: '_edit')]
    public function modifyMyInformations(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository              $userRepository,
        EntityManagerInterface      $em,
                                    $id

    ): Response
    {

        $user = new User();
        $user->setEmail($this->getUser()->getUserIdentifier());
        $user = $userRepository->find($id);

        dump($user);
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        // si le formulaire a été soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $user->getPassword();
            if ($password) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            $em->persist($user);
            $em->flush();
            dump($user);
            return $this->redirectToRoute('user_profil', compact('id'));
        }

        return $this->render('user_profil/editInformations.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
