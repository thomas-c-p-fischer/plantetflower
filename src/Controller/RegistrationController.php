<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\MangoPayService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{


    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    #[Route('/register', name: 'registration_register')]
    public function register(
        User                        $user = null,
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        MailerInterface             $mailer,
        VerifyEmailHelperInterface  $verifyEmailHelper,
        MangoPayService             $service,

    ): Response
    {
        //initialisation de la date du jour.
        $today = new DateTimeImmutable();
        //Creation d'un utilisateur vide pour le set dans le form avec le handle request
        if (!$user) {
            $user = new User();
        }
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setAgreeTerms(true);
            //on ajout ici la date de creation
            $user->setCreatedAt($today);
            //condition pour que si l'utilisateur choisi Owner alors il ne sera pas juste acheteur
            if ($user->isOwner()) {
                $user->setPayer(false);
            } else {
                $user->setPayer(true);
            }
            //hashage du mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ));
            //cr??ation du natural user sur mangoPay, que l'on met dans une variable
            $creationUserMangopay = $service->createNaturalUser($user);
            //cr??ation du wallet sur mangopay
            $service->createWalletForNaturalUser($creationUserMangopay, $user);
            // On utilise la methode generateToken() pour cr??er un jeton unique dans le mail de confirmation
            // puis on ins??re en BDD.
            $user->setToken($this->generateToken());
            $entityManager->persist($user);
            $entityManager->flush();
            // cr??ation d'une signature personnalis??e pour un envoi de mail de verification
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'verify_mail',
                $user->getId(),
                $user->getEmail(),
                array('token' => $user->getToken(), 'id' => $user->getId(), 'mail' => $user->getEmail())
            );
            //Cr??ation du mail
            $email = (new TemplatedEmail())
                ->from('plantetflower@gmail.com')
                ->to($user->getEmail())
                ->subject('Valider votre inscription!')
                //Route du twig qui sert de mod??le au mail
                ->htmlTemplate('registration/confirmation_email.html.twig')
                // pass variables (name => value) to the template
                ->context([
                    'expiration_date' => new \DateTime('+7 days'),
                    'message' => $signatureComponents->getSignedUrl(),
                ]);
            //la methode send() de la class MailerInterface permet d'envoyer un mail a l'utilisateur afin de confirmer
            // son compte et access ?? la connexion.
            $mailer->send($email);
            return $this->redirectToRoute('security_logout');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'editMode' => $user->getId() !== null,
        ]);
    }

    #[Route("/verify/{id}", name: "verify_mail")]
    // verification de la signature mail
    public function verifyUserEmail(
        Request                    $request,
        EntityManagerInterface     $entityManager,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository             $userRepository,

    ): Response
    {
        $id = $request->get('id');
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException();
        }
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('registration_register');
        }

        //On passe l'attribue ?? VRAI et on ins??re en BDD l'utilisateur est reconnu et peut se connecter et naviguer.
        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('security_login');

    }

    /**
     * @throws Exception
     */
    //Creation du Jeton encoder en Base64 en enlevant les caract??res ind??sirables et en les rempla??ant par des -_ et =.
    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }


}
