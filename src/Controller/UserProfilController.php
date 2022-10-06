<?php

namespace App\Controller;

use App\Form\InformationFormType;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use MangoPay\KycDocumentType;
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

        //Récupération de l'utilisateur connecté par son Email.
        $mail = $this->getUser()->getUserIdentifier();
        $userConnect = $userRepository->findOneBy(['email' => $mail]);
        //Création du formulaire d'ajout d'IBAN.
        $informationForm = $this->createForm(InformationFormType::class);
        $informationForm->handleRequest($request);
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
