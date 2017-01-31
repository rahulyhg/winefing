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
use Winefing\ApiBundle\Entity\Subscription;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;



class SubscriptionController extends Controller implements ClassResourceInterface
{

    public function cgetAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscriptions = $repository->findAll();
        return new Response($serializer->serialize($subscriptions, 'json', SerializationContext::create()->setGroups(array('id', 'default'))));
    }
    public function cgetUserGroupAction($userGroup) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscriptions = $repository->findByUserGroup($userGroup);
        return new Response($serializer->serialize($subscriptions, 'json'));
    }
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $subscription = new Subscription();
        $subscription->setFormat($request->request->get('format'));
        $subscription->setActivated($request->request->get('activated'));
        $subscription->setCode($request->request->get('code'));
        $subscription->setUserGroup($request->request->get('userGroup'));
        $validator = $this->get('validator');
        $errors = $validator->validate($subscription);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($subscription);
        $em->flush();
        $json = $serializer->serialize($subscription);
        return new Response($json);
    }

    public function putAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscription = $repository->findOneById($request->request->get("id"));
        $subscription->setFormat($request->request->get('format'));
        $subscription->setActivated($request->request->get('activated'));
        $subscription->setCode($request->request->get('code'));
        $subscription->setUserGroup($request->request->get('userGroup'));
        $validator = $this->get('validator');
        $errors = $validator->validate($subscription);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($subscription);
        $em->flush();
        $json = $serializer->serialize($subscription);
        return new Response($json);
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscription = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($subscription);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscription = $repository->findOneById($request->request->get("id"));
        $subscription->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
    public function patchUserAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscriptions = $repository->findByUserGroup(implode(",", $user->getRoles()));
        $user->setSubscriptions($subscriptions);
        $em->persist($user);
        $em->flush();
    }
}