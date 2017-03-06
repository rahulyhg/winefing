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
use Winefing\ApiBundle\Entity\Iban;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;

class IbanController extends Controller implements ClassResourceInterface
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "Iban" },
     *  description="Return all the companys. Returns a collection of Object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Iban",
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Iban');
        $domains = $repository->findAll();
        $json = $serializer->serialize($domains, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "Iban" },
     *  description="Get a entity by its id.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Iban",
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Iban');
        $company = $repository->findOneById($id);
        $json = $serializer->serialize($company, 'json', SerializationContext::create()->setGroups(array('id','characteristicValues', 'default', 'medias', 'address', 'tags')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "Iban" },
     *  description="Get an iban by user.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Iban",
     *      "groups"={"id", "company", "address"}
     *     },
     *  statusCodes={
     *         200="Returned when successful"
     *     },
     *  requirements={
     *     {
     *          "name"="userId", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     }
     * )
     */
    public function getByUserAction($userId)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Company');
        $company = $repository->findOneByUser($userId);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Iban');
        if($company instanceof Company) {
            $iban = $repository->findOneByCompany($company);
            if(!$iban instanceof Iban) {
                $iban = new Iban();
            }
        } else {
            $company = new Company();
            $iban = new Iban();
        }
        $iban->setCompany($company);

        $json = $serializer->serialize($iban, 'json', SerializationContext::create()->setGroups(array('id','default', 'address', 'company')));
        return new Response($json);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "Iban" },
     *  description="New object.",
     *  input="AppBundle\Form\IbanType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Iban",
     *      "groups"={"id"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $iban = new Iban();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Company');
        $company = $repository->findOneById($request->request->get('company'));
        $iban->setCompany($company);
        $iban->setBic($request->request->get('bic'));
        $iban->setIban($request->request->get('iban'));
        $validator = $this->get('validator');
        $errors = $validator->validate($iban);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($iban);
        $em->flush();
        $json = $serializer->serialize($iban, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'company')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "Iban" },
     *  description="Edit object.",
     *  input="AppBundle\Form\IbanType",
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Iban');
        $iban = $repository->findOneById($request->request->get('id'));
        $iban->setBic($request->request->get('bic'));
        $iban->setIban($request->request->get('iban'));
        $validator = $this->get('validator');
        $errors = $validator->validate($iban);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($iban);
        $em->flush();
        $json = $serializer->serialize($iban, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'company')));
        return new Response($json);
    }
}