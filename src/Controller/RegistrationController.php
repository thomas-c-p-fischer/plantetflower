<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;


class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'registration_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager
//        , ApiUser $ApiUser, ApiWallet $ApiWallet
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->date = new \DateTime('now');
            $user->setCreatedAt($this->date);
            // encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

//            $ApiUser->NewProfil($form);
//            $user->setIdMangopay($ApiUser->userMangoPay->Id);
//            $ApiWallet->NewWallet($ApiUser->userMangoPay->Id);

            $entityManager->persist($user);
            $entityManager->flush();

            // générer une url signée et l’envoyer à l’utilisateur
            $this->emailVerifier->sendEmailConfirmation('verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('myflowerlifeantigaspi@gmail.com', 'Plant & Flower'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // faire tout ce dont vous avez besoin ici, comme envoyer un email

            return $this->redirectToRoute('security_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(
        Request                    $request,
        EntityManagerInterface     $em,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository             $userRepository
    ): Response
    {
        $user = $userRepository->find('id');
        if (!$user) {
            throw $this->createNotFoundException();
        }
        try {
            $verifyEmailHelper->validateEmailConfirmation(
            //$signedUrl = $vESC->getSignedUrl(),
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('registration_register');
        }
        $user->setIsVerified(true);
        $em->persist($user);
        $em->flush();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->addFlash('success', 'Ton adresse email a bien été vérifiée.');
        return $this->redirectToRoute('security_login');
    }
}
