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
use Winefing\ApiBundle\Entity\Domain;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;

class DomainController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="Return all the domains. Returns a collection of Object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Domain",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     }
     * )
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domains = $repository->findAll();
        $json = $serializer->serialize($domains, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="Return all the domain's informations.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Domain",
     *      "groups"={"default", "wineRegion", "medias", "characteristicValues", "properties", "rentals", "user"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     * requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="domain id",
     *          "name"="language", "dataType"="language", "required"=true, "description"="language code"
     *      }
     *   }
     * )
     */
    public function getAllInformationsAction($id, $language) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domains = $repository->findOneById($id);
        foreach($domains as $domain) {
            $domain->setMediaPresentation();
            $domain->setTr($language);
            foreach($domain->getProperties() as $property) {
                $property->setTr($language);
                foreach($property->getRentals() as $rental) {
                    $rental->setTr($language);
                }
            }
        }
        $json = $serializer->serialize($domains, 'json', SerializationContext::create()->setGroups(array('default', 'wineRegion', 'medias', 'characteristicValues', 'properties', 'rentals', 'user')));
        return new Response($json);
    }
    public function cgetExploreAction($language) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domains = $repository->findGroupByWineRegion();
        foreach($domains as $domain) {
            $domain->setMediaPresentation();
            $domain->setTr($language);
        }
        $json = $serializer->serialize($domains, 'json', SerializationContext::create()->setGroups(array('default', 'wineRegion')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="Get a entity by its id.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Domain",
     *      "groups"={"id", "characteristicValues", "default", "address"}
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
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($id);
        $json = $serializer->serialize($domain, 'json', SerializationContext::create()->setGroups(array('id','characteristicValues', 'default', 'medias', 'address')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="Get the user's domain.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Domain",
     *      "groups"={"id", "characteristicValues", "default", "address"}
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
    public function getByUserAction($userId) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneByUser($userId);
        $json = $serializer->serialize($domain, 'json', SerializationContext::create()->setGroups(array('characteristicValues', 'default', 'medias', 'address')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="New object.",
     *  input="AppBundle\Form\DomainType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Domain",
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
        $domain = new Domain();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $domain->setWineRegion($repository->findOneById($request->request->get('wineRegion')));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $domain->setAddress($repository->findOneById($request->request->get('address')));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $domain->setUser($repository->findOneById($request->request->get('user')));
        $domain->setName($request->request->get('name'));
        $domain->setDescription($request->request->get('description'));
        $validator = $this->get('validator');
        $errors = $validator->validate($domain);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($domain);
        $em->flush();
        $json = $serializer->serialize($domain, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="Edit object.",
     *  input="AppBundle\Form\DomainType",
     *  statusCodes={
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $domain->setWineRegion($repository->findOneById($request->request->get('wineRegion')));
        $domain->setName($request->request->get('name'));
        $domain->setDescription($request->request->get('description'));
        $domain->setHistory($request->request->get('history'));
        $validator = $this->get('validator');
        $errors = $validator->validate($domain);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($domain);
        $em->flush();
        $json = $serializer->serialize($domain, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Edit the domain's address.",
     *  views = { "index", "domain" },
     *  parameters={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="domain id"
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $domain->setAddress($repository->findOneById($request->request->get('address')));
        $validator = $this->get('validator');
        $errors = $validator->validate($domain);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($domain);
        $em->flush();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="Delete a domain, only if the domain has no property with rental with rental's order (rentalOrders is empty).",
     *  statusCodes={
     *         204="Returned when no content",
     *         422="The object can't be deleted."
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="property id"
     *      }
     *     },
     *
     * )
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        foreach($domain->getProperties() as $property) {
            foreach($property->getRentals() as $rental) {
                if(!empty($rental->getRentalOrders())) {
                    return new HttpException(422, "You can't delete this domain because some property has rental with related order.");
                }
            }
        }
        $em->remove($domain);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "domain" },
     *  description="Find the domain in the user's wineList. Return a collection of object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Domain",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         204="Returned when no content",
     *         200="Returned when successful",
     *     },
     *  requirements={
     *     {
     *          "name"="userId", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     },
     *
     * )
     */
    public function cgetWineListAction($userId) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $serializer = $this->container->get("jms_serializer");
        $user = $repository->findOneById($userId);
        return new Response($serializer->serialize($user->getWinelist(), 'json', SerializationContext::create()->setGroups(array('default'))));
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","domain", "webPath"},
     *  description="Return the web path for the entity image",
     *  statusCodes={
     *         200="Returned when successful",
     *     }
     *
     * )
     */
    public function getMediaPathAction() {
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('domain_directory'));
        return new Response(json_encode($picturePath));
    }

}