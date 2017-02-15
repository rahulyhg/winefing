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
use Symfony\Component\HttpFoundation\Session\Session;
use JMS\Serializer\Serializer;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $api;
    protected $webPath;
    protected $session;
    protected $serializer;

    public function __construct(Router $router, ApiController $apiController, WebPathController $webPath, Session $session, Serializer $serializer)
    {
        $this->router = $router;
        $this->api = $apiController;
        $this->webPath = $webPath;
        $this->session = $session;
        $this->serializer = $serializer;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if($token->getUser()->isHost()) {
            $response = $this->api->get($this->router->generate('api_get_domain_by_user', array('userId' => $token->getUser()->getId())));
            $domain = $this->serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
            $request->getSession()->set('domainId', $domain->getId());
        }
        return new RedirectResponse($this->router->generate('home'));
    }

}