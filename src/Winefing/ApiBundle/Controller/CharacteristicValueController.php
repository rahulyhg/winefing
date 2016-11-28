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
use Winefing\ApiBundle\Entity\CharacteristicValue;


class CharacteristicValueController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toute les formats possible en base
     * @return Response
     */
    public function cgetAction($domainId)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($domainId);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicValue');
        $characteristicValues = $repository->findByDomain($domain);
        $json = $serializer->serialize($characteristicValues, 'json');
        return new Response($json);
    }

    public function postAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('characteristic'));
        $characteristicValue = new CharacteristicValue();
        $characteristicValue->setCharacteristic($characteristic);
        $characteristicValue->setValue($request->request->get('value'));
        $em->persist($characteristicValue);
        $em->flush();
        return new Response($serializer->serialize($characteristicValue));
    }

    public function putAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicValue');
        $characteristicValue = $repository->findOneById($request->request->get('id'));
        $characteristicValue->setValue($request->request->get('value'));
        $em->persist($characteristicValue);
        $em->flush();
        return new Response($serializer->serialize($characteristicValue));
    }

    public function putDomainAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('domain'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicValue');
        $characteristicValue = $repository->findOneById($request->request->get('characteristicValue'));
        $characteristicValue->addDomain($domain);
        $em->persist($characteristicValue);
        $em->flush();
        return new Response($serializer->serialize($characteristicValue));
    }

    public function putPropertyAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($request->request->get('property'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicValue');
        $characteristicValue = $repository->findOneById($request->request->get('characteristicValue'));
        $characteristicValue->addProperty($property);
        $em->persist($characteristicValue);
        $em->flush();
        return new Response($serializer->serialize($characteristicValue));
    }

    public function putRentalAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('rental'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicValue');
        $characteristicValue = $repository->findOneById($request->request->get('characteristicValue'));
        $characteristicValue->addRental($rental);
        $em->persist($characteristicValue);
        $em->flush();
        return new Response($serializer->serialize($characteristicValue));
    }
}