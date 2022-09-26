<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\User;
use App\Form\InformationFormType;
use App\Form\UserFormType;
use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use MangoPay;

#[Route('/user', 'user')]
class UserProfilController extends AbstractController
{
    private MangoPay\ApiUsers $apiUsers;

    #[Route('/profil', name: '_profil')]
    public function profil(
        \MangoPay\ApiKycDocuments $mangoPayApi,
        UserRepository            $user,
        Request                   $request

    ): Response

    {
        $user->find('id');
        if ($user->getStatus() !== "acheteur") {
            $infoForm = $this->createForm(InformationFormType::class);
            $infoForm->handleRequest($request);
            $KycDocument = new MangoPay\KycDocument();
            $KycDocument->Type = "IDENTITY_PROOF";
            $result = $mangoPayApi->Users->CreateKycDocument($_SESSION["MangoPay"]["UserNatural"], $KycDocument);
            $KycDocumentId = $result->Id;
        }
        return $this->renderform('user_profil/userProfil.html.twig', compact('infoForm'));
    }

    #[Route('/edit', name: '_edit')]
    public function modifyMyInformations(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $em
    ): Response
    {
        // on récupère le formulaire
        $user = $this->getUser();
        $MangoPayId = $user->getIdMangopay();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        // si le formulaire a été soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $MangoPayEdited = $ApiUser->EditProfil($user, $form);
            // on récupère l'image
            $images = $form->get('image')->getData();
            if ($images) {
                // on génère un nv nom de fichier
                $fichier = md5(uniqid()) . '.' . $images->guessExtension();
                // on copie le fichier dans le dossier uploads
                $images->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                $user->setImage(
                    $fichier
                );
            }
            $password = $form->get('plainPassword')->getData();
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
            return $this->redirectToRoute('user_profil');
        }
        $formView = $form->createView();
        return $this->render('user_profil/editInformations.html.twig', [
            'form' => $formView
        ]);
    }
}
