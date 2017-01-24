<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="home")
     */
    public function indexAction(Request $request)
    {
//        var_dump(serialize(new UsernamePasswordToken($this->getUser(), null, 'main', $this->getUser()->getRoles())));
        $api = $this->container->get('winefing.api_controller');

        $response =  $api->post($this->get('router')->generate('api_post_user_token'), array('email' => $this->getUser()->getEmail(), 'plainPassword'=>'Pouca260594!'));
        var_dump($response->getBody()->getContents());
        return $this->render('index.html.twig');
    }
}
