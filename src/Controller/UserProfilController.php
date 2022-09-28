<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InformationFormType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use MangoPay\ApiUsers;
use MangoPay\MangoPayApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/user', 'user')]
class UserProfilController extends AbstractController
{

    #[Route('/profil', name: '_profil')]
    public function profil(
        User    $user,
        Request $request

    ): Response
    {
        $mangoPayApi = new MangoPayApi();
        $mangoPayApi->Config->ClientId = $_ENV['CLIENT_ID'];
        $mangoPayApi->Config->ClientPassword = $_ENV['API_KEY'];
        $mangoPayApi->Config->TemporaryFolder = 'D:\Thomas\Dév\PhpstormProjects\plantetflower/public/temp/';
        $mangoPayApi->Config->BaseUrl = 'https://api.sandbox.mangopay.com/v2.01/'
            . $_ENV['CLIENT_ID'] . '/users/' . $user->getIdMangopay() . 'bankaccounts/iban/';

        $form = $this->createForm(InformationFormType::class);
        $form->handleRequest($request);
        $bankAccount = $request->get('IBAN');
        $apiUser = new ApiUsers($mangoPayApi);

        if ($form->isSubmitted() && $form->isValid())
        {
            $apiUser->CreateBankAccount($user->getIdMangopay(), $bankAccount);
        }

        return $this->renderform('user_profil/userProfil.html.twig');
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
