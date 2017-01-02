<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Winefing\ApiBundle\Entity\Media;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Property;
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\ScopeEnum;
use FOS\RestBundle\Controller\Annotations\Get;
use JMS\Serializer\SerializationContext;


class PropertyController extends Controller implements ClassResourceInterface
{
    public function cgetByUserAction($userId) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $properties = $repository->findByUser($userId);
        foreach($properties as $property) {
            $property->setMediaPresentation();
        }
        return new Response($serializer->serialize($properties, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    public function getMediaPathAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $mediaPath = $webPath->getPath($this->getParameter('property_directory'));
        return new Response($serializer->serialize($mediaPath));
    }
    /**
     * GET Route annotation.
     * @Get("/property/{id}")
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($id);
        $property->setIsAddressDomain();
        $json = $serializer->serialize($property, 'json', SerializationContext::create()->setGroups(array('default', 'medias', 'address')));
        return new Response($json);
    }
    public function getCharacteristicValuesAction($propertyId)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($propertyId);
        $json = $serializer->serialize($property->getCharacteristicValues(), 'json', SerializationContext::create()->setGroups(array('default', 'medias', 'address')));
        return new Response($json);
    }

    public function getMissingCharacteristicsAction($propertyId) {
        $serializer = $this->container->get('jms_serializer');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($propertyId);

        $ids = array();
        foreach($property->getCharacteristicValues() as $characteristicValue) {
            $ids[] = $characteristicValue->getCharacteristic()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristics = $repository->findMissingCharacteristics($ids, ScopeEnum::Property);
        return new Response($serializer->serialize($characteristics, 'json', SerializationContext::create()->setGroups(array('default', 'format', 'trs'))));

    }
    public function getDomainAddressAction($propertyId) {
        $serializer = $this->container->get('jms_serializer');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($propertyId);
        return new Response($serializer->serialize($property->getDomain()->getAddress(), 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $property = new Property();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('domain'));
        $property->setDomain($domain);
        $property->setAddress($domain->getAddress());
        $property->setName($request->request->get("name"));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:PropertyCategory');
        $propertyCategory = $repository->findOneById($request->request->get("propertyCategory"));
        $property->setPropertyCategory($propertyCategory);
        $property->setDescription($request->request->get("description"));
        $validator = $this->get('validator');
        $errors = $validator->validate($property);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($property);
        $em->flush();
        $json = $serializer->serialize($property, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($request->request->all()["id"]);
        $property->setName($request->request->all()["name"]);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:PropertyCategory');
        $propertyCategory = $repository->findOneById($request->request->all()["propertyCategory"]);
        $property->setPropertyCategory($propertyCategory);
        $property->setDescription($request->request->all()["description"]);
        $em->persist($property);
        $em->flush();
        return new Response();
    }
    public function putAddressAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($request->request->get('property'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $property->setAddress($repository->findOneById($request->request->get('address')));
        $validator = $this->get('validator');
        $errors = $validator->validate($property);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($property);
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $webPage = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(!empty($webPage->getWebPageTrs())) {
            throw new BadRequestHttpException("You can't delete this webPage because some translation are related.");
        } else {
            $em->remove($webPage);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

}