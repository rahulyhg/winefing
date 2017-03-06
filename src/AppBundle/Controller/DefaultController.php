<?php

namespace AppBundle\Controller;

use AppBundle\Form\DomainFilterType;
use AppBundle\Form\UserRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Winefing\ApiBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        if ($request->isMethod('POST')) {
            $response =  $api->post($this->get('router')->generate('api_post_email_contact'), $request->request->all());
        }
        return $this->render('contact.html.twig', array());
    }
    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render('about.html.twig', array());
    }
    /**
     * @Route("/help", name="help")
     */
    public function helpAction()
    {
        return $this->render('help.html.twig', array());
    }
}
