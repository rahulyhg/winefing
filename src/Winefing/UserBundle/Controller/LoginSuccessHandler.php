<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 12/01/2017
 * Time: 10:09
 */

namespace Winefing\UserBundle\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Winefing\ApiBundle\Controller\ApiController;
use Winefing\ApiBundle\Controller\WebPathController;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $api;
    protected $webPath;
    protected $container;

    public function __construct(Router $router, ApiController $apiController, WebPathController $webPath, Container $container)
    {
        $this->router = $router;
        $this->api = $apiController;
        $this->webPath = $webPath;
        $this->container = $container;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
//        $response = $this->api->get($this->get('router')->generate('api_get_subscriptions_user_group', array('userGroup'=> UserGroupEnum::Host)));
        //set last login user
        //if admin redirect to dashboard
        //else redirect to the page before connected
//        return $this->redirectToRoute('home');
        return new RedirectResponse($this->router->generate('home'));
    }

}