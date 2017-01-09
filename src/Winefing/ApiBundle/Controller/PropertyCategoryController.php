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
use Winefing\ApiBundle\Entity\PropertyCategory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations\Get;
use JMS\Serializer\SerializationContext;


class PropertyCategoryController extends Controller implements ClassResourceInterface
{
    /**
     * GET Route annotation.
     * @Get("/property-categories")
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:PropertyCategory');
        $propertyCategories = $repository->findAll();
        return new Response($serializer->serialize($propertyCategories, 'json', SerializationContext::create()->setGroups(array('id', 'language', 'default', 'trs'))));
    }

    /**
     * Liste de toutes les tags possible en base
     * @return Response
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:PropertyCategory');
        $propertyCategory = $repository->findOneById($id);
        return new Response($serializer->serialize($propertyCategory, 'json'));
    }
    /**
     * Create or update a tag from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $propertyCategory = new PropertyCategory();
        $validator = $this->get('validator');
        $errors = $validator->validate($propertyCategory);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($propertyCategory);
        $em->flush();
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize($propertyCategory, 'json', SerializationContext::create()->setGroups(array('id'))));
    }

    /**
     * Delete a tag
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:PropertyCategory');
        $propertyCategory = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($propertyCategory);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

}