<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->render('index.html.twig');
    }
}
