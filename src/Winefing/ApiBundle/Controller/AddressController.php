<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Address;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;


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
    public function cgetByUserAction($userId)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $addresses = $repository->findAllByUser($userId);
        if(count($addresses)>0) {
            $json = $serializer->serialize($addresses, 'json',  SerializationContext::create()->setGroups(array('default')));
            return new Response($json);
        }
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
        $address->setName($request->request->get('name'));
        $address->setAdditionalInformation($request->request->get('additionalInformation'));
        $address->setLat($request->request->get('lat'));
        $address->setLng($request->request->get('lng'));
        $address->setName($request->request->get('name'));
        $address->setFormattedAddress($request->request->get('formattedAddress'));
        $validator = $this->get('validator');
        $errors = $validator->validate($address);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($address);
        $em->flush();
        $json = $serializer->serialize($address, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }
    public function postCopyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($request->request->get('id'));
        $newAddress = clone $address;
        $newAddress->clearUsers();
        $validator = $this->get('validator');
        $errors = $validator->validate($newAddress);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($newAddress);
        $em->flush();
        $json = $serializer->serialize($newAddress, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($request->request->get('id'));
        $address->setStreetAddress($request->request->get('streetAddress'));
        $address->setRoute($request->request->get('route'));
        $address->setPolitical($request->request->get('political'));
        $address->setCountry($request->request->get('country'));
        $address->setPostalCode($request->request->get('postalCode'));
        $address->setLocality($request->request->get('locality'));
        $address->setName($request->request->get('name'));
        $address->setAdditionalInformation($request->request->get('additionalInformation'));
        $address->setLat(1.0);
        $address->setLng(1.0);
        $address->setName($request->request->get('name'));
        $address->setFormattedAddress($request->request->get('formattedAddress'));
        $validator = $this->get('validator');
        $errors = $validator->validate($address);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($address);
        $em->flush();
        return new Response();
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
    public function patchUserAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($request->request->get('address'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $address->addUser($user);
        $validator = $this->get('validator');
        $errors = $validator->validate($address);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($address);
        $em->flush($address);
        return new Response(json_encode($request->request->get('user')));
    }

    public function getByPropertyAction($propertyId) {
        $serializer = $this->container->get('jms_serializer');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($propertyId);
        return new Response($serializer->serialize($property->getDomain()->getAddress(), 'json', SerializationContext::create()->setGroups(array('default'))));
    }

}