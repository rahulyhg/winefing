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
use Winefing\ApiBundle\Entity\Property;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use JMS\Serializer\SerializationContext;


class PropertyController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "property" },
     *  description="Return all the user's properties. Returns a collection of Object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Property",
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
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $properties = $repository->findByUser($userId);
        foreach($properties as $property) {
            $property->setMediaPresentation();
        }
        return new Response($serializer->serialize($properties, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "property" },
     *  description="Return all the properties with basic informations in the user's language",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Property",
     *      "groups"={"domain","domain", "default", "characteristicValues", "propertyCategory"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="language", "dataType"="string", "required"=true, "description"="language code (fr, en...)"
     *      }
     *     }
     * )
     * @Get("mobile/properties/{language}")
     */
    public function cgetByLanguageAction($language) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $properties = $repository->findAll();
        $i = 0;
        foreach($properties as $property) {
            if(count($property->getRentals()) == 0) {
                unset($properties[$i]);
            } else {
                $property->setMediaPresentation();
                $property->setTr($language);
                $property->setMinMaxPrice();
                $property->getDomain()->setTr($language);
            }
            $i++;
        }
        return new Response($serializer->serialize($properties, 'json', SerializationContext::create()->setGroups(array('domain','domain','default', 'characteristicValues', 'propertyCategory'))));
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "property" },
     *  description="Get a entity by its id.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Property",
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

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "property" },
     *  description="New object.",
     *  input="AppBundle\Form\PropertyType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Property",
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
            return new HttpException(400, $errorsString);
        }
        $em->persist($property);
        $em->flush();
        return new Response($serializer->serialize($property, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "property" },
     *  description="Edit object.",
     *  input="AppBundle\Form\PropertyType",
     *  statusCodes={
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
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
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "property" },
     *  description="Edit the property's address.",
     *  parameters={
     *     {
     *          "name"="property", "dataType"="integer", "required"=true, "description"="property id"
     *      },
     *     {
     *        "name"="address", "dataType"="integer", "required"=true, "description"="address id"
     *     }
     *   },
     *  statusCodes={
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function patchAddressAction(Request $request)
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
            return new HttpException(400, $errorsString);
        }
        $em->persist($property);
        $em->flush();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "property" },
     *  description="Delete a property, only if the property has no rental with rental's order (rentalOrders is empty).",
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        foreach($$property->getRentals() as $rental) {
            if(!empty($rental->getOrders())) {
                return new HttpException(422, "You can't delete this property because some rental has related order.");
            }
        }
        $em->remove($property);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","property", "webPath"},
     *  description="Return the web path for the entity image",
     *  statusCodes={
     *         200="Returned when successful",
     *     }
     *
     * )
     */
    public function getMediaPathAction() {
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('property_directory'));
        return new Response(json_encode($picturePath));
    }

}