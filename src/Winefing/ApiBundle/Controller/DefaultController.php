<?php

namespace Winefing\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('WinefingApiBundle:Default:index.html.twig');
    }
}
