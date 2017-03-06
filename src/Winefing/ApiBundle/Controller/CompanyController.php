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
use Winefing\ApiBundle\Entity\Company;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;

class CompanyController extends Controller implements ClassResourceInterface
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "company" },
     *  description="Return all the companys. Returns a collection of Object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\company",
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Company');
        $domains = $repository->findAll();
        $json = $serializer->serialize($domains, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "company" },
     *  description="Get a entity by its id.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Company",
     *      "groups"={"id", "characteristicValues", "default", "address", "tags"}
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Company');
        $company = $repository->findOneById($id);
        $json = $serializer->serialize($company, 'json', SerializationContext::create()->setGroups(array('id','characteristicValues', 'default', 'medias', 'address', 'tags')));
        return new Response($json);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "company" },
     *  description="New object.",
     *  input="AppBundle\Form\DomainType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Company",
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
        $company = new Company();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($request->request->get('address'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $company->setUser($user);
        $address = clone $address;
        $company->setAddress($address);
        $company->setName($request->request->get('name'));
        $validator = $this->get('validator');
        $errors = $validator->validate($company);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($company);
        $em->flush();
        $json = $serializer->serialize($company, 'json', SerializationContext::create()->setGroups(array('id', 'default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "company" },
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Company');
        $company = $repository->findOneById($request->request->get('id'));
        $company->setName($request->request->get('name'));
        $validator = $this->get('validator');
        $errors = $validator->validate($company);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($company);
        $em->flush();
        $json = $serializer->serialize($company, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
}