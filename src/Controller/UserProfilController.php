<?php

namespace App\Controller;

use App\Entity\Annonce;
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
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/user', 'user')]
class UserProfilController extends AbstractController
{


    #[Route('/profil', name: '_profil')]
    public function profil(
        Request                $request,
        ApiUser                $apiUser,
        ApiKYCDocument         $apiKYCDocument,
        AnnonceRepository      $annonceRepository,
        EntityManagerInterface $em,
        UserRepository         $userRepository,
        HttpClientInterface    $httpClient
    ): Response
    {
        $ResultMango = "";
        $user = $this->getUser();
        $annonces = $this->$annonceRepository->findAll();
        $annonce = $this->$em->getRepository(Annonce::class)->findAll();
        $IdMangoPay = $user->getIdMangopay();
        $form = $this->createForm(InformationFormType::class);
        $form->handleRequest($request);
        // acheteur
        if ($user->getStatus() !== "") {
            $KYCStatus = $ApiKYCDoc->GetKYCDocumentById($IdMangoPay);
            if ($KYCStatus === false) {
                $StatusKYC = "TO_ASK";
            } else if ($KYCStatus['Type'] === "IDENTITY_PROOF") {
                if ($KYCStatus['Status'] === "VALIDATION_ASKED") {
                    $StatusKYC = "VALIDATION_ASKED";
                } else if ($KYCStatus['Status'] === "VALIDATED") {
                    $StatusKYC = "VALIDATED";
                } else if ($KYCStatus['Status'] === "REFUSED") {
                    if ($KYCStatus['RefusedReasonType'] === "DOCUMENT_UNREADABLE") {
                    } else if ($KYCStatus['RefusedReasonType'] === "DOCUMENT_NOT_ACCEPTED") {
                        $StatusKYC = "REFUSED_DOCUMENT_NOT_ACCEPTED";
                    } else if ($KYCStatus['RefusedReasonType'] === "DOCUMENT_HAS_EXPIRED") {
                        $StatusKYC = "REFUSED_DOCUMENT_HAS_EXPIRED";
                    } else if ($KYCStatus['RefusedReasonType'] === "DOCUMENT_INCOMPLETE") {
                        $StatusKYC = "REFUSED_DOCUMENT_INCOMPLETE";
                    } else if ($KYCStatus['RefusedReasonType'] === "DOCUMENT_MISSING") {
                        $StatusKYC = "REFUSED_DOCUMENT_MISSING";
                    } else if ($KYCStatus['RefusedReasonType'] === "DOCUMENT_DOES_NOT_MATCH_USER_DATA") {
                        $StatusKYC = "REFUSED_DOCUMENT_DOES_NOT_MATCH_USER_DATA";
                    } else if ($KYCStatus['RefusedReasonType'] === "SPECIFIC_CASE") {
                        $StatusKYC = "REFUSED_SPECIFIC_CASE";
                    } else if ($KYCStatus['RefusedReasonType'] === "DOCUMENT_FALSIFIED") {
                        $StatusKYC = "REFUSED_DOCUMENT_FALSIFIED";
                    } else if ($KYCStatus['RefusedReasonType'] === "UNDERAGE_PERSON") {
                        $StatusKYC = "REFUSED_UNDERAGE_PERSON";
                    }
                }
            }
        } else {
            $StatusKYC = "";
        }
        $recto = $form['KYCrecto']->getData();
        $verso = $form['KYCverso']->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $ApiIban->NewIban($form['IBAN']->getData(), $form['BIC']->getData(), $user);

            $KycDocument = $ApiKYCDoc->NewKYCDocument($user->getIdMangopay(), $recto->getPathname(), $verso->getPathname());
            $ResultMango = $ApiKYCDoc->SubmitKYCDocument($user->getIdMangopay(), $KycDocument);
            return $this->render('user_profil/userProfil.html.twig', [
                'user' => $user,
                'annonce' => $annonce,
                'infoForm' => $form->createView(),
                'KycStatus' => $KYCStatus['Status'],
                'errorMessages' => $ResultMango->RefusedReasonMessage,
                'message' => 'vos informations ont bien été transmises...elles sont en cours de traitement par mangopay.',
                'verso' => $verso->getPathname(),
                'recto' => $recto->getPathname()
            ]);
        };
        $verso = $recto = "";
        return $this->render('user_profil/userProfil.html.twig', [
            'user' => $user,
            'annonce' => $annonce,
            'infoForm' => $form->createView(),
            'KycStatus' => $StatusKYC,
            'errorMessages' => $ResultMango,
            'message' => '',
            'verso' => $verso,
            'recto' => $recto
        ]);
    }

    #[Route('/edit', name: '_edit')]
    public function modifyMyInformations(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        ApiUser                     $ApiUser,
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
