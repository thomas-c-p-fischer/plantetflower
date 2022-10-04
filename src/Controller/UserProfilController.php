<?php

namespace App\Controller;


use App\Form\InformationFormType;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/user', 'user')]
class UserProfilController extends AbstractController
{


    #[Route('/profil/{id}', name: '_profil')]
    public function profil(

        Request         $request,
        MangoPayService $service,
        UserRepository  $userRepository,


    ): Response
    {

        $mail = $this->getUser()->getUserIdentifier();
        $informationForm = $this->createForm(InformationFormType::class);
        $informationForm->handleRequest($request);
        $iban = $informationForm['IBAN']->getData();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        if ($informationForm->isSubmitted() && $informationForm->isValid()) {
            $service->createBankAccount($userConnect, $iban);
        }

        return $this->renderform('user_profil/userProfil.html.twig', compact('informationForm', 'mail'));
    }

//    #[Route('/edit', name: '_edit')]
//    public function modifyMyInformations(
//        Request                     $request,
//        UserPasswordHasherInterface $userPasswordHasher,
//        EntityManagerInterface      $em
//    ): Response
//    {
//        // on récupère le formulaire
//        $user = $this->getUser();
//        $MangoPayId = $user->getIdMangopay();
//        $form = $this->createForm(UserFormType::class, $user);
//        $form->handleRequest($request);
//        // si le formulaire a été soumis
//        if ($form->isSubmitted() && $form->isValid()) {
//            $MangoPayEdited = $ApiUser->EditProfil($user, $form);
//            // on récupère l'image
//            $images = $form->get('image')->getData();
//            if ($images) {
//                // on génère un nv nom de fichier
//                $fichier = md5(uniqid()) . '.' . $images->guessExtension();
//                // on copie le fichier dans le dossier uploads
//                $images->move(
//                    $this->getParameter('images_directory'),
//                    $fichier
//                );
//                $user->setImage(
//                    $fichier
//                );
//            }
//            $password = $form->get('plainPassword')->getData();
//            if ($password) {
//                $user->setPassword(
//                    $userPasswordHasher->hashPassword(
//                        $user,
//                        $form->get('plainPassword')->getData()
//                    )
//                );
//            }
//            $em->persist($user);
//            $em->flush();
//            return $this->redirectToRoute('user_profil');
//        }
//        $formView = $form->createView();
//        return $this->render('user_profil/editInformations.html.twig', [
//            'form' => $formView
//        ]);
//    }
}
