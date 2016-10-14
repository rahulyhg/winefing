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
use Winefing\ApiBundle\Entity\Tag;
use Winefing\ApiBundle\Entity\TagTr;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\LanguageEnum;



class TagController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toutes les tags possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tags = $repository->findAll();
        return new Response($serializer->serialize($tags, 'json'));
    }

    /**
     * Liste de toutes les tags possible en base
     * @return Response
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tag = $repository->findOneById($id);
        return new Response($serializer->serialize($tag, 'json'));
    }
    /**
     * Create or update a tag from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tag = new Tag();
        $validator = $this->get('validator');
        $errors = $validator->validate($tag);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($tag);
        $em->flush();
        $serializer = $this->container->get('winefing.serializer_controller');
        return new Response($serializer->serialize($tag));
    }

    /**
     * Delete a tag
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tag = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($tag);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

}