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
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Address;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class AddressController extends Controller implements ClassResourceInterface
{

    public function getAction($domainId)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($domainId);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneByDomain($domain);
        $json = $serializer->serialize($address);
        return new Response($json);
    }
    public function getByUserAction($userId)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($userId);
        $json = $serializer->serialize($address);
        return new Response($json);
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $address = new Address();
        $address->setStreetAddress($request->request->get('streetAddress'));
        $address->setRoute($request->request->get('route'));
        $address->setPolitical($request->request->get('political'));
        $address->setCountry($request->request->get('country'));
        $address->setPostalCode($request->request->get('postalCode'));
        $address->setLocality($request->request->get('locality'));
        $address->setLat(1.0);
        $address->setLng(1.0);
        $address->setName($request->request->get('name'));
        $address->setFormattedAddress($request->request->get('formattedAddress'));
        $validator = $this->get('validator');
        $errors = $validator->validate($address);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($address);
        $em->flush();
        $json = $serializer->serialize($address, 'json');
        return new Response($json);
    }
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($request->request->get('id'));
        $address->setStreetAddress($request->request->get('streetAddress'));
        $address->setRoute($request->request->get('route'));
        $address->setPolitical($request->request->get('political'));
        $address->setCountry($request->request->get('country'));
        $address->setPostalCode($request->request->get('postalCode'));
        $address->setLocality($request->request->get('locality'));
        $address->setLat(1.0);
        $address->setLng(1.0);
        $address->setName($request->request->get('name'));
        $address->setFormattedAddress($request->request->get('formattedAddress'));
        $validator = $this->get('validator');
        $errors = $validator->validate($address);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($address);
        $em->flush();
        $json = $serializer->serialize($address, 'json');
        return new Response($json);
    }
    /**
     * Delete a web page
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $webPage = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($webPage);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

}