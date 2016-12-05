<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\EventRequestType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\File\File;
use Winefing\ApiBundle\Entity\EventRequest;
use Winefing\ApiBundle\Entity\CharacteristicpropertyValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class EventRequestController extends Controller
{
    /**
     * @Route("/event/request", name="event_request")
     */
    public function getAction() {
        return $this->render('user/eventRequest/index.html.twig');

    }

    /**
     * @Route("/event/request/new", name="event_request_new")
     */
    public function newAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $eventRequest = new EventRequest();
        $eventRequestForm = $this->createForm(EventRequestType::class, $eventRequest);
        $eventRequestForm->handleRequest($request);
        if ($eventRequestForm->isSubmitted() && $eventRequestForm->isValid()) {
            $api->post($this->get('_router')->generate('api_post_event_request'), $request->request->all()['event_request']);
        }
        return $this->render('user/eventRequest/new.html.twig', array('eventRequestForm' => $eventRequestForm->createView()));
    }
}