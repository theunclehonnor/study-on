<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use App\Exception\ClientException;
use App\Form\RegistrationType;
use App\Model\UserDto;
use App\Security\User;
use App\Service\BillingClient;
use App\Security\AppCustomAuthenticator;
use App\Service\DecodingJwt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('course_index');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by 
        the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register")
     * @throws BillingUnavailableException
     */
    public function register(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        AppCustomAuthenticator $authenticator,
        BillingClient $billingClient,
        DecodingJwt $decodingJwt
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('profile');
        }

        $userDto = new UserDto();
        $form = $this->createForm(RegistrationType::class, $userDto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            try {
                $userDto = $billingClient->register($userDto);
                $user = User::fromDto($userDto, $decodingJwt);
//            } catch (ClientException $e) {
//                return $this->render('security/register.html.twig', [
//                    'form' => $form->createView(),
//                    'errors' => $e->getMessage(),
//                ]);
//            } catch (BillingUnavailableException $e){
//                throw new BillingUnavailableException($e->getMessage());
//            }

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main'
            );
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
