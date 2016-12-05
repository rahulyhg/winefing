<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Winefing\ApiBundle\Entity\EventRequest;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class EventRequestController extends Controller implements ClassResourceInterface
{
    /**
     * Create or update a characteristicCategoryTr from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $eventRequest = new EventRequest();
        $eventRequest->setBudget($request->request->get("budget"));
        $eventRequest->setStartDate($request->request->get("startDate"));
        $eventRequest->setEndDate($request->request->get("endDate"));
        $eventRequest->setDuration($request->request->get("duration"));
        $eventRequest->setEmail($request->request->get("email"));
        $eventRequest->setPhoneNumber($request->request->get("phoneNumber"));
        $eventRequest->setDescription($request->request->get("description"));
        $eventRequest->setPeopleNumber($request->request->get("peopleNumber"));

        $validator = $this->get('validator');
        $errors = $validator->validate($eventRequest);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($eventRequest);
        $em->flush();
        $json = $serializer->serialize($eventRequest, 'json');
        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     */
    function putAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategoryTr');
        $characteristicCategoryTr =  $repository->findOneById($request->request->get("id"));
        $characteristicCategoryTr->setName(ucfirst(strtolower($request->request->get("name"))));

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristicCategoryTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristicCategoryTr);
        $em->flush();
        $json = $serializer->serialize($characteristicCategoryTr);
        return new Response($json);
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($id);
        if(count($characteristicCategory->getCharacteristics()) > 0) {
            throw new BadRequestHttpException("You can't delete this category because some characteristics are present.");
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($characteristicCategory);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($request->request->get("id"));
        $characteristicCategory->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}