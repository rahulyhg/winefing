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
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\CharacteristicDomainValue;
use Winefing\ApiBundle\Entity\MediaFormatEnum;


class CharacteristicDomainValueController extends Controller implements ClassResourceInterface
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicDomainValue');
        $characteristicDomainValues = $repository->findByDomain($domain);
        $json = $serializer->serialize($characteristicDomainValues, 'json');
        return new Response($json);
    }

    public function postAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('domain'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('characteristic'));
        $characteristicDomainValue = new CharacteristicDomainValue();
        $characteristicDomainValue->setDomain($domain);
        $characteristicDomainValue->setCharacteristic($characteristic);
        $characteristicDomainValue->setValue($request->request->get('value'));
        $em->persist($characteristicDomainValue);
        $em->flush();
        return new Response($serializer->serialize($characteristicDomainValue, 'json'));
    }

    public function putAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('domain'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('characteristic'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicDomainValue');
        $characteristicDomainValue = $repository->findOneById($request->request->get('id'));
        $characteristicDomainValue->setDomain($domain);
        $characteristicDomainValue->setCharacteristic($characteristic);
        $characteristicDomainValue->setValue($request->request->get('value'));
        $em->persist($characteristicDomainValue);
        $em->flush();
        return new Response($serializer->serialize($characteristicDomainValue, 'json'));
    }
}