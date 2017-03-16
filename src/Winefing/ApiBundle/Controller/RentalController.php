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
use Winefing\ApiBundle\Entity\Rental;
use FOS\RestBundle\Controller\Annotations\Get;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;


class RentalController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get the minimum or maximum price of all the rental on the website ",
     *  views = { "index", "rental" },
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"id", "default", "medias", "characteristicValues", "property"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="rental id"
     *      }
     *     }
     * )
     * GET Route annotation.
     */
    public function getByPriceAction($order)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneWithPrice($order);
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('id', 'default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a entity by its id.",
     *  views = { "index", "rental" },
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"id", "default", "medias", "characteristicValues", "property"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="rental id"
     *      }
     *     }
     * )
     * GET Route annotation.
     * @Get("rental/{id}")
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($id);
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'medias', 'characteristicValues', 'property')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get all rental's property.",
     *  views = { "index", "rental" },
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"id", "default", "characteristicValues", "medias"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="property", "dataType"="integer", "required"=true, "description"="property id",
     *          "name"="language", "dataType"="string", "required"=true, "description"="language code"
     *      }
     *     }
     * )
     * GET Route annotation.
     * @Get("mobile/rentals/property/{property}/{language}")
     */
    public function getByPropertyAction($property, $language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rentals = $repository->findByProperty($property);
        foreach($rentals as $rental) {
            $rental->setMediaPresentation();
            $rental->setTr($language);
        }
        $json = $serializer->serialize($rentals, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'characteristicValues', 'medias')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "rental" },
     *  description="Get a entity by its id and by the language of the user.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"id", "default", "characteristicValues", "property"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="rental id"
     *      },
     *     {
     *          "name"="language", "dataType"="integer", "required"=true, "description"="language code"
     *      }
     *  }
     * )
     * GET Route annotation.
     * @Get("rental/{id}/language/{language}")
     */
    public function getByLanguageAction($id, $language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($id);
        $rental->setTr($language);
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'medias', 'characteristicValues', 'property')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental" },
     *  description="Return all the user's rentals. Returns a collection of Object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="domainId", "dataType"="integer", "required"=true, "description"="domain id"
     *      }
     *     }
     * )
     */
    public function cgetByDomainAction($domainId) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rentals = $repository->findWithDomain($domainId);
        foreach($rentals as $rental) {
            $rental->setMediaPresentation();
        }
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($rentals, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental" },
     *  description="Return all the user's rentals. Returns a collection of Object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="userId", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     }
     * )
     */
    public function cgetByUserAction($userId) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rentals = $repository->findByUser($userId);
        foreach($rentals as $rental) {
            $rental->setMediaPresentation();
        }
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($rentals, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental" },
     *  description="Returns a collection of Object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *     }
     * )
     */
    public function cgetAction() {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rentals = $repository->findAll();
        foreach($rentals as $rental) {
            $rental->setMediaPresentation();
        }
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($rentals, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    public function cgetByPropertyAction($propertyId) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($propertyId);
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($property->getRentals(), 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental" },
     *  description="New object.",
     *  input="AppBundle\Form\RentalType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"id"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rental = new Rental();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $rental->setProperty($repository->findOneById($request->request->get('property')));
        $rental->setName($request->request->get('name'));
        $rental->setDescription($request->request->get('description'));
        $rental->setPeopleNumber($request->request->get('peopleNumber'));
        $rental->setMinimumRentalPeriod($request->request->get('minimumRentalPeriod'));
        $rental->setRentalCategory($request->request->get('rentalCategory'));
        $rental->setPrice($request->request->get('price'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rental);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($rental);
        $em->flush();
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental" },
     *  description="Edit object.",
     *  input="AppBundle\Form\RentalType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Rental",
     *      "groups"={"id"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $rental->setProperty($repository->findOneById($request->request->get('property')));
        $rental->setName($request->request->get('name'));
        $rental->setDescription($request->request->get('description'));
        $rental->setPeopleNumber($request->request->get('peopleNumber'));
        $rental->setMinimumRentalPeriod($request->request->get('minimumRentalPeriod'));
        $rental->setRentalCategory($request->request->get('rentalCategory'));
        $rental->setPrice($request->request->get('price'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rental);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($rental);
        $em->flush();
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental" },
     *  description="Delete a rental, only if the rental has no rental's order (rentalOrders is empty).",
     *  statusCodes={
     *         204="Returned when no content",
     *         422="The object can't be deleted."
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="rental id"
     *      }
     *     },
     *
     * )
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(count($rental->getRentalOrders()) > 0) {
            $rental->setActivated(0);
            $em->persist($rental);
        } else {
            $em->remove($rental);
        }
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental", "webPath"},
     *  description="Return the web path for the entity image",
     *  statusCodes={
     *         200="Returned when successful",
     *     }
     *
     * )
     */
    public function getMediaPathAction() {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('rental_directory'));
        return new Response($serializer->serialize($picturePath));
    }
}