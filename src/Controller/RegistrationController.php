<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\Authenticator;
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
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'registration_register')]
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface  $userAuthenticator,
        Authenticator               $authenticator,
        EntityManagerInterface      $em
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $em->persist($user);
            $em->flush();

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
        return $this->render('registration/register.html.twig', compact('form'));
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
