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
use Winefing\ApiBundle\Entity\Box;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\BoxItem;
use Winefing\ApiBundle\Entity\CreditCard;


class CreditCardController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les languages possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CreditCard');
        $creditCards = $repository->findAll();
        return new Response($serializer->serialize($creditCards, 'json', SerializationContext::create()->setGroups(array('default'))));
    }
    /**
     * Liste de tout les languages possible en base
     * @return Response
     */
    public function cgetByUserAction($userId)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CreditCard');
        $creditCards = $repository->findByUser($userId);
        return new Response($serializer->serialize($creditCards, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $creditCard = new CreditCard();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $creditCard->setUser($repository->findOneById($request->request->get('user')));
        $creditCard->setOwner($request->request->get('owner'));
        $creditCard->setLemonWayId($request->request->get('lemonWayId'));
        $validator = $this->get('validator');
        $errors = $validator->validate($creditCard);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($creditCard);
        $em->flush();
        return new Response($serializer->serialize($creditCard, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CreditCard');
        $creditCard = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($creditCard);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}