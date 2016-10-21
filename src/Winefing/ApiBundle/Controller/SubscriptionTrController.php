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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\SubscriptionTr;


class SubscriptionTrController extends Controller implements ClassResourceInterface
{
    /**
     * Create or update a subscriptionTr from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $subscriptionTr = new SubscriptionTr();

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscription = $repository->findOneById($request->request->get('subscription'));
        if(empty($subscription)) {
            throw new \BadMethodCallException('The subscriptionId is mandatory.');
        }
        $subscriptionTr->setSubscription($subscription);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $subscriptionTr->setLanguage($repository->findOneById($request->request->get("language")));
        $subscriptionTr->setName(ucfirst(strtolower($request->request->get("name"))));
        $subscriptionTr->setDescription(ucfirst($request->request->get("description")));
        $validator = $this->get('validator');
        $errors = $validator->validate($subscriptionTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($subscriptionTr);
        $em->flush();
        $json = $serializer->serialize($subscriptionTr);
        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     */
    function putAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:SubscriptionTr');
        $subscriptionTr = $repository->findOneById($request->request->get('id'));
        if(empty($subscriptionTr)) {
            throw new \BadMethodCallException('The subscriptionId is mandatory.');
        }
        $subscriptionTr->setName(ucfirst(strtolower($request->request->get("name"))));
        $subscriptionTr->setDescription(ucfirst($request->request->get("description")));
        $validator = $this->get('validator');
        $errors = $validator->validate($subscriptionTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($subscriptionTr);
        $em->flush();
        $json = $serializer->serialize($subscriptionTr);
        return new Response($json);
    }
}