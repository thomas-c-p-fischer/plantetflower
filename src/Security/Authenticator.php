<?php

namespace App\Security;

use App\Entity\User;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class Authenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {

    }

    public function authenticate(Request $request,): Passport
    {

        // Création d'un recaptcha
        $recaptcha =new ReCaptcha('6LfPU0chAAAAAD9KPiBbrOdI8_sKPx55PMMcfI35');
        // Vérifie si le recaptcha a été validé ou non
        $recaptchaValid = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

        // Si le recaptcha n'a pas été validé
        if(!$recaptchaValid->isSuccess()) {

            // Message du recaptcha non validé
            throw new CustomUserMessageAuthenticationException('Veuillez valider le captcha');

        // Si le recaptcha a été validé
        }else{

            $email = $request->request->get('email', '');
            $request->getSession()->set(Security::LAST_USERNAME, $email);
            return new Passport(
                new UserBadge($email),
                new PasswordCredentials($request->request->get('password', '')),
                [
                    new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                ]
            );
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('home_homepage'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
